<?php

use App\Filament\Teacher\Pages\ComposeStudentEmail;
use App\Models\LearningClass;
use App\Models\QuizAttempt;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\SchoolActivityNotification;
use App\Services\SchoolEmailNotificationService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function composeEmailSchool(): School
{
    return School::where('code', 'HIA-2026')->firstOrFail();
}

function composeEmailClass(): LearningClass
{
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function composeEmailUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function composeEmailStudent(string $email): Student
{
    return Student::whereHas('user', fn ($query) => $query->where('email', $email))->firstOrFail();
}

function composeEmailConfigureSchool(): School
{
    $school = composeEmailSchool();
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    return $school->fresh();
}

test('custom messages reach the student and the parent email on file', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $student = composeEmailStudent('student1@example.com');
    $student->update(['parent_email' => 'guardian1@example.com']);

    Notification::fake();

    $dispatched = app(SchoolEmailNotificationService::class)->sendCustomStudentMessage(
        composeEmailClass(),
        collect([$student->fresh()]),
        'Please revise the algebra section before Friday.',
        null,
        null,
        true,
        'Nimal Perera',
    );

    expect($dispatched)->toBe(2);

    Notification::assertSentTo(
        composeEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $n): bool => $n->subject === 'Message from Nimal Perera: 10-B Science & Physics'
            && in_array('Please revise the algebra section before Friday.', $n->lines, true)
            && in_array('Class: 10-B Science & Physics', $n->lines, true)
    );

    Notification::assertSentOnDemand(
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $n, array $channels, object $notifiable): bool => ($notifiable->routes['mail'] ?? null) === 'guardian1@example.com'
            && in_array('Student: '.$student->user->name, $n->lines, true)
            && in_array('Please revise the algebra section before Friday.', $n->lines, true),
    );
});

test('students without a parent email on file only receive their own copy', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $student = composeEmailStudent('student1@example.com');
    $student->update(['parent_email' => null]);

    Notification::fake();

    $dispatched = app(SchoolEmailNotificationService::class)->sendCustomStudentMessage(
        composeEmailClass(),
        collect([$student->fresh()]),
        'Class test message.',
        null,
        null,
        true,
        'Nimal Perera',
    );

    expect($dispatched)->toBe(1);
    Notification::assertCount(1);
});

test('the parent copy can be turned off per message', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $student = composeEmailStudent('student1@example.com');
    $student->update(['parent_email' => 'guardian1@example.com']);

    Notification::fake();

    $dispatched = app(SchoolEmailNotificationService::class)->sendCustomStudentMessage(
        composeEmailClass(),
        collect([$student->fresh()]),
        'Student only message.',
        null,
        null,
        false,
        'Nimal Perera',
    );

    expect($dispatched)->toBe(1);

    Notification::assertSentTo(composeEmailUser('student1@example.com'), SchoolActivityNotification::class);
    Notification::assertCount(1);
});

test('assignment submission scores are included per student', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $class = composeEmailClass();
    $assignment = $class->assignments()->firstOrFail();
    $assignment->update(['max_score' => 100]);

    $gradedStudent = composeEmailStudent('student1@example.com');
    $gradedStudent->update(['parent_email' => null]);

    $submission = $gradedStudent->assignmentSubmissions()
        ->where('assignment_id', $assignment->getKey())
        ->first();

    expect($submission)->not->toBeNull();

    $submission->update(['score' => 88.5]);

    Notification::fake();

    app(SchoolEmailNotificationService::class)->sendCustomStudentMessage(
        $class,
        collect([$gradedStudent->fresh()]),
        'Scores are in.',
        $assignment,
        null,
        false,
        'Nimal Perera',
    );

    Notification::assertSentTo(
        composeEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $n): bool => in_array('Assignment: '.$assignment->title, $n->lines, true)
            && in_array('Your score: 88.5 / 100', $n->lines, true)
    );
});

test('quiz scores are included per student and ungraded work is reported clearly', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $class = composeEmailClass();
    $quiz = $class->quizzes()->firstOrFail();
    $quiz->update(['total_points' => 20]);

    $attemptStudent = composeEmailStudent('student1@example.com');
    $attemptStudent->update(['parent_email' => null]);

    QuizAttempt::query()->updateOrCreate(
        [
            'quiz_id' => $quiz->getKey(),
            'student_id' => $attemptStudent->getKey(),
        ],
        [
            'attempt_number' => 1,
            'started_at' => now(),
            'completed_at' => now(),
            'score' => 18,
            'percentage' => 90,
            'is_passed' => true,
            'status' => 'submitted',
        ],
    );

    $noAttemptStudent = composeEmailStudent('student2@example.com');
    $noAttemptStudent->update(['parent_email' => null]);

    QuizAttempt::query()
        ->where('quiz_id', $quiz->getKey())
        ->where('student_id', $noAttemptStudent->getKey())
        ->delete();

    Notification::fake();

    app(SchoolEmailNotificationService::class)->sendCustomStudentMessage(
        $class,
        collect([$attemptStudent->fresh(), $noAttemptStudent->fresh()]),
        'Quiz results are available.',
        null,
        $quiz,
        false,
        'Nimal Perera',
    );

    Notification::assertSentTo(
        composeEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $n): bool => in_array('Quiz: '.$quiz->title, $n->lines, true)
            && in_array('Your score: 18 / 20 (90%) - Passed', $n->lines, true)
    );

    Notification::assertSentTo(
        composeEmailUser('student2@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $n): bool => in_array('Your attempt: No completed attempt recorded', $n->lines, true)
    );
});

test('ungraded assignment submissions are reported as awaiting grading', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $class = composeEmailClass();
    $assignment = $class->assignments()->firstOrFail();

    $student = composeEmailStudent('student1@example.com');
    $student->update(['parent_email' => null]);

    $submission = $student->assignmentSubmissions()
        ->where('assignment_id', $assignment->getKey())
        ->firstOrFail();

    $submission->update(['score' => null]);

    Notification::fake();

    app(SchoolEmailNotificationService::class)->sendCustomStudentMessage(
        $class,
        collect([$student->fresh()]),
        'Progress update.',
        $assignment,
        null,
        false,
        'Nimal Perera',
    );

    Notification::assertSentTo(
        composeEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $n): bool => in_array('Your submission: Submitted, not graded yet', $n->lines, true)
    );
});

test('custom messages are not dispatched when the school has no smtp settings', function () {
    $this->seed();

    $student = composeEmailStudent('student1@example.com');
    $student->update(['parent_email' => 'guardian1@example.com']);

    Notification::fake();

    $dispatched = app(SchoolEmailNotificationService::class)->sendCustomStudentMessage(
        composeEmailClass(),
        collect([$student->fresh()]),
        'Should not be sent.',
        null,
        null,
        true,
        'Nimal Perera',
    );

    expect($dispatched)->toBe(0);
    Notification::assertNothingSent();
});

test('the composer page sends a message to the selected students of the chosen class', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $class = composeEmailClass();
    $student = composeEmailStudent('student1@example.com');
    $student->update(['parent_email' => 'guardian1@example.com']);

    $teacher = composeEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(ComposeStudentEmail::class)
        ->fillForm([
            'learning_class_id' => $class->getKey(),
            'student_ids' => [$student->getKey()],
            'message' => 'Reminder about the upcoming quiz.',
            'include_parents' => true,
        ])
        ->call('send')
        ->assertHasNoFormErrors();

    Notification::assertSentTo(
        composeEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $n): bool => in_array('Reminder about the upcoming quiz.', $n->lines, true)
    );

    Notification::assertSentOnDemand(
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $n, array $channels, object $notifiable): bool => ($notifiable->routes['mail'] ?? null) === 'guardian1@example.com'
    );
});

test('the composer page ignores students that are not active in the chosen class', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $class = composeEmailClass();

    $outsider = composeEmailStudent('student9@example.com');
    $outsider->update(['parent_email' => 'outsider-guardian@example.com']);

    $teacher = composeEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(ComposeStudentEmail::class)
        ->fillForm([
            'learning_class_id' => $class->getKey(),
            'student_ids' => [$outsider->getKey()],
            'message' => 'Should reach nobody.',
            'include_parents' => true,
        ])
        ->call('send');

    Notification::assertNothingSent();
});

test('the composer page rejects an assignment that belongs to a different class', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $class = composeEmailClass();
    $otherClass = LearningClass::where('name', '!=', '10-B Science & Physics')->firstOrFail();
    $foreignAssignment = $otherClass->assignments()->firstOrFail();

    $student = composeEmailStudent('student1@example.com');
    $student->update(['parent_email' => null]);

    $teacher = composeEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(ComposeStudentEmail::class)
        ->fillForm([
            'learning_class_id' => $class->getKey(),
            'student_ids' => [$student->getKey()],
            'message' => 'Cross-class assignment should be ignored.',
            'assignment_id' => $foreignAssignment->getKey(),
            'include_parents' => false,
        ])
        ->call('send')
        ->assertHasFormErrors(['assignment_id']);

    Notification::assertNothingSent();
});

test('the composer page refuses a class that belongs to another teacher', function () {
    $this->seed();
    composeEmailConfigureSchool();

    $otherTeacherClass = LearningClass::where('name', '!=', '10-B Science & Physics')
        ->whereDoesntHave('teachers', fn ($query) => $query->whereKey(composeEmailUser('teacher1@example.com')->teacher->getKey()))
        ->firstOrFail();

    $outsider = composeEmailStudent('student3@example.com');
    $outsider->update(['parent_email' => 'cross-teacher-guardian@example.com']);

    $teacher = composeEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(ComposeStudentEmail::class)
        ->fillForm([
            'learning_class_id' => $otherTeacherClass->getKey(),
            'student_ids' => [$outsider->getKey()],
            'message' => 'Should reach nobody.',
            'include_parents' => true,
        ])
        ->call('send')
        ->assertHasFormErrors(['learning_class_id']);

    Notification::assertNothingSent();
});

test('parent emails use a parent friendly greeting and footer', function () {
    $this->seed();
    $school = composeEmailConfigureSchool();
    $student = composeEmailStudent('student1@example.com');

    $notification = new SchoolActivityNotification(
        $school->getKey(),
        'Message from Nimal Perera: 10-B Science & Physics',
        'Nimal Perera has sent a message.',
        ['Student: '.$student->user->name],
    );

    $mail = $notification->toMail(Notification::route('mail', 'guardian1@example.com'));
    $data = $mail->toArray();

    expect($data['greeting'])->toBe('Hello,')
        ->and($mail->from)->toBe([$school->email, $school->name])
        ->and($mail->mailer)->toStartWith("school-{$school->getKey()}-");
});

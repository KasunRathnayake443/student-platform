<?php

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningClass;
use App\Models\School;
use App\Models\User;
use App\Services\SchoolActivityNotification;
use App\Services\SchoolEmailNotificationService;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function schoolEmailFeatureSchool(): School
{
    return School::where('code', 'HIA-2026')->firstOrFail();
}

function schoolEmailFeatureClass(): LearningClass
{
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function schoolEmailFeatureUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function schoolEmailFeatureConfigure(School $school): School
{
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    return $school->fresh();
}

test('class activity notifications target active students only', function () {
    $this->seed();
    schoolEmailFeatureConfigure(schoolEmailFeatureSchool());
    Notification::fake();

    $class = schoolEmailFeatureClass();
    $quiz = $class->quizzes()->firstOrFail();
    $assignment = $class->assignments()->firstOrFail();
    $lesson = $class->lessons()->firstOrFail();
    $service = app(SchoolEmailNotificationService::class);

    $service->quizCreated($quiz);
    $service->assignmentCreated($assignment);
    $service->lessonCreated($lesson);

    $studentOne = schoolEmailFeatureUser('student1@example.com');
    $studentTwo = schoolEmailFeatureUser('student2@example.com');

    Notification::assertSentTo(
        [$studentOne, $studentTwo],
        SchoolActivityNotification::class,
    );
    Notification::assertNotSentTo(schoolEmailFeatureUser('student9@example.com'), SchoolActivityNotification::class);
    Notification::assertNotSentTo(schoolEmailFeatureUser('student5@example.com'), SchoolActivityNotification::class);
    Notification::assertCount(6);

    Notification::assertSentTo(
        $studentOne,
        SchoolActivityNotification::class,
        function (SchoolActivityNotification $notification) use ($quiz): bool {
            return $notification->schoolId === schoolEmailFeatureSchool()->getKey()
                && $notification->subject === "New quiz: {$quiz->title}"
                && $notification->actionUrl === route('filament.student.pages.quiz-attempt').'?quiz='.$quiz->getKey();
        },
    );
});

test('inactive enrollments do not receive class activity notifications', function () {
    $this->seed();
    schoolEmailFeatureConfigure(schoolEmailFeatureSchool());
    $class = schoolEmailFeatureClass();
    $class->enrollments()
        ->whereHas('student', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('email', 'student2@example.com')))
        ->update(['status' => 'inactive']);
    Notification::fake();

    app(SchoolEmailNotificationService::class)->quizCreated($class->quizzes()->firstOrFail());

    Notification::assertSentTo(schoolEmailFeatureUser('student1@example.com'), SchoolActivityNotification::class);
    Notification::assertNotSentTo(schoolEmailFeatureUser('student2@example.com'), SchoolActivityNotification::class);
    Notification::assertCount(1);
});

test('unconfigured schools do not dispatch activity notifications', function () {
    $this->seed();
    Notification::fake();

    app(SchoolEmailNotificationService::class)->quizCreated(schoolEmailFeatureClass()->quizzes()->firstOrFail());

    Notification::assertNothingSent();
});

test('grading notifications target the submitted student and include the grade', function () {
    $this->seed();
    schoolEmailFeatureConfigure(schoolEmailFeatureSchool());
    $assignment = Assignment::where('title', 'Quadratic Problem Set & Vertex Form Applications')->firstOrFail();
    $submission = AssignmentSubmission::where('assignment_id', $assignment->getKey())
        ->whereHas('student', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('email', 'student2@example.com')))
        ->firstOrFail();
    $submission->update([
        'score' => 88.5,
        'feedback' => '<p>Good algebra and clear reasoning.</p>',
        'status' => 'graded',
    ]);
    Notification::fake();

    app(SchoolEmailNotificationService::class)->assignmentGraded($submission->fresh());

    $student = schoolEmailFeatureUser('student2@example.com');
    Notification::assertSentTo(
        $student,
        SchoolActivityNotification::class,
        function (SchoolActivityNotification $notification) use ($assignment): bool {
            return $notification->subject === "Assignment graded: {$assignment->title}"
                && in_array('Score: 88.5 / 100', $notification->lines, true)
                && in_array('Feedback: Good algebra and clear reasoning.', $notification->lines, true)
                && $notification->actionUrl === route('filament.student.pages.assignment').'?assignment='.$assignment->getKey();
        },
    );
    Notification::assertNotSentTo(schoolEmailFeatureUser('student1@example.com'), SchoolActivityNotification::class);
    Notification::assertCount(1);
});

test('mail messages use the school sender and keep content unescaped for markdown', function () {
    $this->seed();
    $school = schoolEmailFeatureConfigure(schoolEmailFeatureSchool());
    $student = schoolEmailFeatureUser('student1@example.com');
    $notification = new SchoolActivityNotification(
        $school->getKey(),
        'New lesson: Science & Physics',
        'Your teacher has published a new lesson.',
        ['Class: Science & Physics'],
        route('filament.student.pages.lesson').'?lesson=1',
    );

    $message = $notification->toMail($student);
    $data = $message->toArray();

    expect($notification)->toBeInstanceOf(ShouldBeEncrypted::class)
        ->and($message->from)->toBe([$school->email, $school->name])
        ->and($data['greeting'])->toBe("Hello {$student->name},")
        ->and($data['introLines'])->toContain('Class: Science & Physics')
        ->and($data['actionUrl'])->toBe(route('filament.student.pages.lesson').'?lesson=1')
        ->and($message->mailer)->toStartWith("school-{$school->getKey()}-");
});

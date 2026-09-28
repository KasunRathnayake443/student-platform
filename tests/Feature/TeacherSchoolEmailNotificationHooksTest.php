<?php

use App\Filament\Teacher\Resources\Assignments\Pages\ViewAssignment;
use App\Filament\Teacher\Resources\Assignments\RelationManagers\SubmissionsRelationManager;
use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\AssignmentsRelationManager;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\LessonsRelationManager;
use App\Filament\Teacher\Resources\Quizzes\Pages\CreateQuiz;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningClass;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\SchoolActivityNotification;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function hookSchool(): School
{
    return School::where('code', 'HIA-2026')->firstOrFail();
}

function hookClass(): LearningClass
{
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function hookUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function hookConfigureSchool(): School
{
    $school = hookSchool();
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    return $school->fresh();
}

function hookQuizQuestions(): array
{
    return [
        [
            'question_text' => 'What does a force measurer report?',
            'points' => 1,
            'options' => [
                ['option_text' => 'Force', 'is_correct' => true],
                ['option_text' => 'Velocity', 'is_correct' => false],
            ],
        ],
    ];
}

test('creating a teacher quiz dispatches queued student notifications', function () {
    $this->seed();
    hookConfigureSchool();
    $this->actingAs(hookUser('teacher1@example.com'));
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::test(CreateQuiz::class)
        ->fillForm([
            'learning_class_id' => hookClass()->getKey(),
            'title' => 'Hooked Physics Quiz',
            'passing_percentage' => 50,
            'max_attempts' => 1,
            'available_immediately' => true,
            'availability_type' => 'immediate',
            'end_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'questions' => hookQuizQuestions(),
            'teacher_ids' => [auth()->user()->teacher->getKey()],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Notification::assertSentTo(
        [hookUser('student1@example.com'), hookUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('creating a teacher assignment dispatches queued student notifications', function () {
    $this->seed();
    hookConfigureSchool();
    $this->actingAs(hookUser('teacher1@example.com'));
    Notification::fake();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => hookClass(),
        'pageClass' => ViewLearningClass::class,
    ])->callTableAction('createAssignment', data: [
        'title' => 'Hooked Physics Assignment',
        'allowed_submission_types' => ['text'],
        'end_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
    ]);

    Notification::assertSentTo(
        [hookUser('student1@example.com'), hookUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('creating a teacher lesson dispatches queued student notifications', function () {
    $this->seed();
    hookConfigureSchool();
    $this->actingAs(hookUser('teacher1@example.com'));
    Notification::fake();

    Livewire::test(LessonsRelationManager::class, [
        'ownerRecord' => hookClass(),
        'pageClass' => ViewLearningClass::class,
    ])->callTableAction('createLesson', data: [
        'title' => 'Hooked Physics Lesson',
        'sort_order' => 4,
        'is_published' => true,
    ]);

    Notification::assertSentTo(
        [hookUser('student1@example.com'), hookUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('grading a teacher assignment dispatches a notification to the submitted student', function () {
    $this->seed();
    hookConfigureSchool();
    $this->actingAs(hookUser('teacher1@example.com'));
    Notification::fake();

    $assignment = Assignment::where(
        'title',
        'Physics Lab Report: Measuring Acceleration (F = ma)',
    )->firstOrFail();
    $student = Student::whereHas(
        'classes',
        fn ($query) => $query->where('learning_classes.id', $assignment->learning_class_id),
    )->whereNotIn(
        'id',
        AssignmentSubmission::where('assignment_id', $assignment->getKey())->select('student_id'),
    )->firstOrFail();
    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->getKey(),
        'student_id' => $student->getKey(),
        'content' => '<p>Hooked lab report.</p>',
        'submitted_at' => now()->subHour(),
        'is_late' => false,
        'status' => 'submitted',
    ]);

    Livewire::test(SubmissionsRelationManager::class, [
        'ownerRecord' => $assignment,
        'pageClass' => ViewAssignment::class,
    ])->callTableAction('grade', $submission, data: [
        'score' => 45,
        'feedback' => '<p>Well structured report.</p>',
    ]);

    Notification::assertSentTo($student->user, SchoolActivityNotification::class);
    Notification::assertCount(1);
});

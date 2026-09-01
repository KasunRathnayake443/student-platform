<?php

use App\Filament\Student\Pages\AssignmentView;
use App\Filament\Teacher\Resources\Assignments\Pages\EditAssignment;
use App\Models\Assignment;
use App\Models\LearningClass;
use App\Models\Teacher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function taTeacher(): User
{
    return User::where('email', 'teacher2@example.com')->firstOrFail();
}

function taStudent(): User
{
    return User::where('email', 'student1@example.com')->firstOrFail();
}

function taPanel(): void
{
    Filament::setCurrentPanel('teacher');
}

function taScheduledAssignment(): Assignment
{
    $class = LearningClass::where('name', '10-A Mathematics')->firstOrFail();
    $teacher = Teacher::where('employee_no', 'EMP-T-1002')->firstOrFail();

    return Assignment::create([
        'learning_class_id' => $class->id,
        'teacher_id' => $teacher->id,
        'title' => 'Availability Repro: Scheduled',
        'description' => 'Repro',
        'max_score' => 10,
        'availability_type' => 'scheduled',
        'start_at' => now()->addDays(1),
        'end_at' => now()->addDays(3),
        'allow_late_submissions' => false,
        'allowed_submission_types' => ['text'],
        'is_published' => true,
    ]);
}

test('teacher edit page hydrates the Available Immediately toggle from the stored availability type', function () {
    $this->seed();
    $this->actingAs(taTeacher());
    taPanel();

    $assignment = taScheduledAssignment();

    Livewire::test(EditAssignment::class, ['record' => $assignment->getKey()])
        ->assertSuccessful()
        ->assertFormSet(['available_immediately' => false]);
});

test('teacher can switch an existing scheduled assignment to immediate and the start date is cleared', function () {
    $this->seed();
    $this->actingAs(taTeacher());
    taPanel();

    $assignment = taScheduledAssignment();

    Livewire::test(EditAssignment::class, ['record' => $assignment->getKey()])
        ->assertSuccessful()
        ->fillForm([
            'available_immediately' => true,
            'end_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'allowed_submission_types' => ['text'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $assignment->refresh();

    expect($assignment->availability_type)->toBe('immediate')
        ->and($assignment->start_at)->toBeNull();
});

test('an assignment saved as immediate is instantly available to students', function () {
    $this->seed();
    $this->actingAs(taTeacher());
    taPanel();

    $assignment = taScheduledAssignment();

    Livewire::test(EditAssignment::class, ['record' => $assignment->getKey()])
        ->assertSuccessful()
        ->fillForm([
            'available_immediately' => true,
            'end_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'allowed_submission_types' => ['text'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $assignment->refresh();

    Filament::setCurrentPanel('student');
    $this->actingAs(taStudent());

    Livewire::withQueryParams(['assignment' => $assignment->getKey()])
        ->test(AssignmentView::class)
        ->assertSuccessful()
        ->assertDontSee('SUBMISSIONS ARE LOCKED')
        ->assertDontSee('NOT STARTED YET')
        ->assertSee('Submit your assignment');
});

<?php

use App\Filament\Teacher\Resources\Assignments\Pages\ViewAssignment;
use App\Filament\Teacher\Resources\Assignments\RelationManagers\SubmissionsRelationManager;
use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\AssignmentsRelationManager;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function asgUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function asgTeacherByNo(string $employeeNo): Teacher
{
    return Teacher::where('employee_no', $employeeNo)->firstOrFail();
}

function sciencePhysicsClass(): LearningClass
{
    // teacher1's class: 10-B Science & Physics
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function seededPhysicsAssignment(): Assignment
{
    return Assignment::where(
        'title',
        'Physics Lab Report: Measuring Acceleration (F = ma)'
    )->firstOrFail();
}

test('assignments tab lists existing assignments and links to the teacher panel', function () {
    $this->seed();
    $this->actingAs(asgUser('teacher1@example.com'));

    $assignment = seededPhysicsAssignment();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => sciencePhysicsClass(),
        'pageClass' => ViewLearningClass::class,
    ])
        ->assertSuccessful()
        ->assertSee($assignment->title)
        ->assertSee('Create Assignment')
        ->assertSee('/teacher/assignments/');
});

test('created assignments are automatically assigned to the authenticated teacher', function () {
    $this->seed();
    $me = asgTeacherByNo('EMP-T-1001');
    $this->actingAs(asgUser('teacher1@example.com'));

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => sciencePhysicsClass(),
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('createAssignment', data: [
            'title' => 'Kinematics Worksheet',
            'allowed_submission_types' => ['pdf'],
            'end_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

    $assignment = Assignment::where('title', 'Kinematics Worksheet')->firstOrFail();

    expect($assignment->teacher_id)->toBe($me->getKey())
        ->and($assignment->availability_type)->toBe('immediate')
        ->and($assignment->teachers()->pluck('teachers.id')->toArray())->toBe([$me->getKey()]);
});

test('an assignment can be co-assigned to other teachers of the same class', function () {
    $this->seed();
    $me = asgTeacherByNo('EMP-T-1001');
    $coTeacher = asgTeacherByNo('EMP-T-1002');
    $this->actingAs(asgUser('teacher1@example.com'));

    $class = sciencePhysicsClass();
    $class->teachers()->syncWithoutDetaching([$coTeacher->getKey()]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('createAssignment', data: [
            'title' => 'Shared Marking Exercise',
            'allowed_submission_types' => ['text'],
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'teacher_ids' => [$me->getKey(), $coTeacher->getKey()],
        ]);

    $assignment = Assignment::where('title', 'Shared Marking Exercise')->firstOrFail();
    $assignees = $assignment->teachers()->orderBy('teachers.id')->pluck('teachers.id');

    expect($assigneeIds = $assignees->all())
        ->and(in_array($me->getKey(), $assigneeIds))->toBeTrue()
        ->and(in_array($coTeacher->getKey(), $assigneeIds))->toBeTrue();
});

test('teachers outside the class cannot be assigned to an assignment', function () {
    $this->seed();
    $me = asgTeacherByNo('EMP-T-1001');
    $outsider = asgTeacherByNo('EMP-T-1003');
    $this->actingAs(asgUser('teacher1@example.com'));

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => sciencePhysicsClass(),
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('createAssignment', data: [
            'title' => 'Hijacked Assignment',
            'allowed_submission_types' => ['pdf'],
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'teacher_ids' => [$me->getKey(), $outsider->getKey()],
        ]);

    expect(Assignment::where('title', 'Hijacked Assignment')->exists())->toBeFalse();
});

test('assigned teachers can see submissions and grade them', function () {
    $this->seed();
    $me = asgTeacherByNo('EMP-T-1001');
    $this->actingAs(asgUser('teacher1@example.com'));

    $assignment = seededPhysicsAssignment();

    // A pending submission awaiting grading
    $student = Student::whereHas('classes', fn ($q) => $q->where('learning_classes.id', $assignment->learning_class_id))
        ->whereNotIn('id', AssignmentSubmission::where('assignment_id', $assignment->getKey())->select('student_id'))
        ->firstOrFail();

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->getKey(),
        'student_id' => $student->getKey(),
        'content' => '<p>My lab report.</p>',
        'submitted_at' => now()->subHour(),
        'is_late' => false,
        'status' => 'submitted',
    ]);

    $this->get("/teacher/assignments/{$assignment->getKey()}")
        ->assertOk();

    Livewire::test(SubmissionsRelationManager::class, [
        'ownerRecord' => $assignment,
        'pageClass' => ViewAssignment::class,
    ])
        ->assertSuccessful()
        ->assertSee($student->user->name)
        ->callTableAction('grade', $submission, data: [
            'score' => 45,
            'feedback' => '<p>Well structured report.</p>',
        ]);

    $submission->refresh();

    expect((float) $submission->score)->toBe(45.0)
        ->and($submission->status)->toBe('graded')
        ->and($submission->graded_by)->toBe($me->getKey())
        ->and($submission->feedback)->toContain('Well structured report.');
});

test('class teachers who are not assigned to the assignment cannot see its submissions', function () {
    $this->seed();
    $this->actingAs(asgUser('teacher2@example.com'));

    // Sarah joins the class but is NOT assigned to the physics assignment
    $class = sciencePhysicsClass();
    $class->teachers()->syncWithoutDetaching([asgTeacherByNo('EMP-T-1002')->getKey()]);

    $assignment = seededPhysicsAssignment();

    // A submission exists on this assignment...
    $student = Student::whereHas('classes', fn ($q) => $q->where('learning_classes.id', $assignment->learning_class_id))
        ->whereNotIn('id', AssignmentSubmission::where('assignment_id', $assignment->getKey())->select('student_id'))
        ->firstOrFail();

    AssignmentSubmission::create([
        'assignment_id' => $assignment->getKey(),
        'student_id' => $student->getKey(),
        'content' => '<p>My lab report.</p>',
        'submitted_at' => now()->subHour(),
        'is_late' => false,
        'status' => 'submitted',
    ]);

    // She can still open the assignment itself as a class teacher
    $this->get("/teacher/assignments/{$assignment->getKey()}")
        ->assertOk();

    // ...but her submissions tab receives no data at all
    Livewire::test(SubmissionsRelationManager::class, [
        'ownerRecord' => $assignment,
        'pageClass' => ViewAssignment::class,
    ])
        ->assertSuccessful()
        ->assertDontSee($student->user->name);
});

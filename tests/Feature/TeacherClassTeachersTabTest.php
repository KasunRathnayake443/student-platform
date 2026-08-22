<?php

use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\TeachersRelationManager;
use App\Models\LearningClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function seedTeacher(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function teacherByEmployeeNo(string $employeeNo): Teacher
{
    return Teacher::where('employee_no', $employeeNo)->firstOrFail();
}

function scienceAndPhysicsClass(): LearningClass
{
    // teacher1's class: 10-B Science & Physics
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

test('teachers tab lists co-teachers and offers no management actions', function () {
    $this->seed();
    $this->actingAs(seedTeacher('teacher1@example.com'));

    $class = scienceAndPhysicsClass();

    // Sarah Connor teaches another class; make her a co-teacher of this one.
    $coTeacher = teacherByEmployeeNo('EMP-T-1002');
    $class->teachers()->syncWithoutDetaching([$coTeacher->getKey()]);

    Livewire::test(TeachersRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->assertSuccessful()
        ->assertSee($coTeacher->user->name)
        ->assertSee($coTeacher->employee_no)
        // No way for a teacher to add or remove teachers from the class
        ->assertDontSee('Assign Existing Teacher')
        ->assertDontSee('Create New Teacher')
        ->assertDontSee('Remove Teacher')
        // View action links to the read-only profile in the teacher panel
        ->assertSee('/teacher/teachers/'.$coTeacher->getKey());
});

test('teacher profile page is visible to co-teachers and is read-only', function () {
    $this->seed();
    $this->actingAs(seedTeacher('teacher1@example.com'));

    $class = scienceAndPhysicsClass();
    $coTeacher = teacherByEmployeeNo('EMP-T-1002');
    $class->teachers()->syncWithoutDetaching([$coTeacher->getKey()]);

    $this->get("/teacher/teachers/{$coTeacher->getKey()}")
        ->assertOk()
        ->assertSee('Teacher Profile')
        ->assertSee($coTeacher->user->name)
        ->assertSee($coTeacher->user->email)
        ->assertSee($coTeacher->phone);
});

test('teacher profiles are not accessible without a shared class', function () {
    $this->seed();
    $this->actingAs(seedTeacher('teacher1@example.com'));

    // Alan Turing shares no classes with teacher1
    $stranger = teacherByEmployeeNo('EMP-T-1005');

    $this->get("/teacher/teachers/{$stranger->getKey()}")
        ->assertNotFound();
});

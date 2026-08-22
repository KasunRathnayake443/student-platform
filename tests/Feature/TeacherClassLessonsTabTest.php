<?php

use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\LessonsRelationManager;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function ltUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function ltTeacherByNo(string $employeeNo): Teacher
{
    return Teacher::where('employee_no', $employeeNo)->firstOrFail();
}

function physicsClass(): LearningClass
{
    // teacher1's class: 10-B Science & Physics
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

test('lessons tab lists existing lessons and links to the teacher panel', function () {
    $this->seed();
    $this->actingAs(ltUser('teacher1@example.com'));

    $class = physicsClass();

    Livewire::test(LessonsRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->assertSuccessful()
        ->assertSee("Newton's Laws of Motion & Dynamics")
        ->assertSee('Create Lesson')
        ->assertSee('/teacher/lessons/');
});

test('created lessons are automatically assigned to the authenticated teacher', function () {
    $this->seed();
    $me = ltTeacherByNo('EMP-T-1001');
    $this->actingAs(ltUser('teacher1@example.com'));

    $class = physicsClass();

    Livewire::test(LessonsRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('createLesson', data: [
            'title' => 'Forces Practical',
            'sort_order' => 3,
        ]);

    $lesson = Lesson::where('title', 'Forces Practical')->firstOrFail();

    expect($lesson->teacher_id)->toBe($me->getKey())
        ->and($lesson->learning_class_id)->toBe($class->getKey());
});

test('a lesson can be handed to a co-teacher of the same class', function () {
    $this->seed();
    $this->actingAs(ltUser('teacher1@example.com'));

    $class = physicsClass();
    $coTeacher = ltTeacherByNo('EMP-T-1002');
    $class->teachers()->syncWithoutDetaching([$coTeacher->getKey()]);

    Livewire::test(LessonsRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('createLesson', data: [
            'title' => 'Co-Taught Intro',
            'teacher_id' => $coTeacher->getKey(),
        ]);

    $lesson = Lesson::where('title', 'Co-Taught Intro')->firstOrFail();

    expect($lesson->teacher_id)->toBe($coTeacher->getKey());
});

test('teachers outside the class cannot be assigned to a lesson', function () {
    $this->seed();
    $this->actingAs(ltUser('teacher1@example.com'));

    $class = physicsClass();
    $outsider = ltTeacherByNo('EMP-T-1003');

    Livewire::test(LessonsRelationManager::class, [
        'ownerRecord' => $class,
        'pageClass' => ViewLearningClass::class,
    ])
        ->callTableAction('createLesson', data: [
            'title' => 'Hijacked Lesson',
            'teacher_id' => $outsider->getKey(),
        ]);

    expect(Lesson::where('title', 'Hijacked Lesson')->exists())->toBeFalse();
});

test('own-class lessons can be viewed and edited without delete access', function () {
    $this->seed();
    $this->actingAs(ltUser('teacher1@example.com'));

    $lesson = Lesson::where('title', "Newton's Laws of Motion & Dynamics")->firstOrFail();

    $this->get("/teacher/lessons/{$lesson->getKey()}")
        ->assertOk()
        ->assertSee($lesson->title);

    $this->get("/teacher/lessons/{$lesson->getKey()}/edit")
        ->assertOk()
        ->assertDontSee('Delete Lesson');
});

test('lessons of other teachers classes are inaccessible', function () {
    $this->seed();
    $this->actingAs(ltUser('teacher1@example.com'));

    // A class taught only by Sarah Connor
    $foreignClass = LearningClass::create([
        'grade_id' => physicsClass()->grade_id,
        'name' => '10-C Foreign Class',
        'medium' => 'English',
        'is_active' => true,
    ]);
    $foreignClass->teachers()->syncWithoutDetaching([
        ltTeacherByNo('EMP-T-1002')->getKey(),
    ]);

    $lesson = Lesson::create([
        'learning_class_id' => $foreignClass->getKey(),
        'teacher_id' => ltTeacherByNo('EMP-T-1002')->getKey(),
        'title' => 'Secret Algebra',
        'sort_order' => 0,
        'is_published' => true,
    ]);

    $this->get("/teacher/lessons/{$lesson->getKey()}")
        ->assertNotFound();

    $this->get("/teacher/lessons/{$lesson->getKey()}/edit")
        ->assertNotFound();
});

<?php

use App\Filament\Resources\Schools\Pages\ViewSchool as AdminViewSchool;
use App\Filament\Resources\Schools\RelationManagers\TeachersRelationManager as AdminSchoolTeachersRelationManager;
use App\Filament\Resources\Teachers\Pages\ListTeachers as AdminListTeachers;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\EditTeacher as SchoolEditTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\ListTeachers as SchoolListTeachers;
use App\Models\Assignment;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function tmSchool(string $code): School
{
    return School::where('code', $code)->firstOrFail();
}

function tmUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function tmClass(School $school, string $name): LearningClass
{
    return LearningClass::whereHas('grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->where('name', $name)
        ->firstOrFail();
}

test('a super admin deleting a teacher removes the account and all teacher data', function () {
    $this->seed();
    $this->actingAs(tmUser('admin1@example.com'));
    Filament::setCurrentPanel('admin');

    $teacher = tmUser('teacher1@example.com')->teacher;
    $teacherId = $teacher->getKey();
    $userId = $teacher->user_id;

    expect(Lesson::where('teacher_id', $teacherId)->exists())->toBeTrue()
        ->and(Assignment::where('teacher_id', $teacherId)->exists())->toBeTrue()
        ->and(Quiz::where('teacher_id', $teacherId)->exists())->toBeTrue()
        ->and($teacher->schools()->exists())->toBeTrue()
        ->and($teacher->classes()->exists())->toBeTrue();

    Livewire::test(AdminListTeachers::class)
        ->callTableAction('delete', $teacher);

    expect(Teacher::find($teacherId))->toBeNull()
        ->and(User::find($userId))->toBeNull()
        ->and(Lesson::where('teacher_id', $teacherId)->exists())->toBeFalse()
        ->and(Assignment::where('teacher_id', $teacherId)->exists())->toBeFalse()
        ->and(Quiz::where('teacher_id', $teacherId)->exists())->toBeFalse();
});

test('a school admin removing a teacher only detaches the selected school and its classes', function () {
    $this->seed();
    $this->actingAs(tmUser('schooladmin1@example.com'));
    Filament::setCurrentPanel('school-admin');

    $hia = tmSchool('HIA-2026');
    $osc = tmSchool('OSC-2026');
    $teacher = tmUser('teacher5@example.com')->teacher;
    $userId = $teacher->user_id;

    $hiaClass = tmClass($hia, '11-A ICT & Computing');
    $oscClass = tmClass($osc, '9-B Computer Studies');

    Livewire::test(SchoolListTeachers::class)
        ->assertTableActionDoesNotExist('delete', null, $teacher)
        ->assertTableActionExists('removeFromSchool', null, $teacher)
        ->callTableAction('removeFromSchool', $teacher, ['schools' => [$hia->getKey()]]);

    $teacher->refresh();

    expect(Teacher::find($teacher->getKey()))->not->toBeNull()
        ->and(User::find($userId))->not->toBeNull()
        ->and($teacher->schools()->where('schools.id', $hia->getKey())->exists())->toBeFalse()
        ->and($teacher->schools()->where('schools.id', $osc->getKey())->exists())->toBeTrue()
        ->and($teacher->classes()->where('learning_classes.id', $hiaClass->getKey())->exists())->toBeFalse()
        ->and($teacher->classes()->where('learning_classes.id', $oscClass->getKey())->exists())->toBeTrue();
});

test('a super admin removing a teacher from a school keeps their teaching content', function () {
    $this->seed();
    $this->actingAs(tmUser('admin1@example.com'));
    Filament::setCurrentPanel('admin');

    $hia = tmSchool('HIA-2026');
    $teacher = tmUser('teacher5@example.com')->teacher;

    $lessonIds = Lesson::where('teacher_id', $teacher->getKey())->pluck('id')->all();
    $assignmentIds = Assignment::where('teacher_id', $teacher->getKey())->pluck('id')->all();
    $quizIds = Quiz::where('teacher_id', $teacher->getKey())->pluck('id')->all();

    expect($lessonIds)->not->toBeEmpty()
        ->and($assignmentIds)->not->toBeEmpty()
        ->and($quizIds)->not->toBeEmpty();

    Livewire::test(AdminSchoolTeachersRelationManager::class, [
        'ownerRecord' => $hia,
        'pageClass' => AdminViewSchool::class,
    ])
        ->assertTableActionExists('removeFromSchool', null, $teacher)
        ->assertTableActionDoesNotExist('delete', null, $teacher)
        ->callTableAction('removeFromSchool', $teacher);

    $teacher->refresh();

    expect($teacher->schools()->where('schools.id', $hia->getKey())->exists())->toBeFalse()
        ->and($teacher->classes()->whereHas(
            'grade',
            fn ($query) => $query->where('school_id', $hia->getKey()),
        )->exists())->toBeFalse()
        ->and(Teacher::find($teacher->getKey()))->not->toBeNull()
        ->and(Lesson::whereKey($lessonIds)->count())->toBe(count($lessonIds))
        ->and(Assignment::whereKey($assignmentIds)->count())->toBe(count($assignmentIds))
        ->and(Quiz::whereKey($quizIds)->count())->toBe(count($quizIds));
});

test('a school admin cannot remove a teacher from a school outside their scope', function () {
    $this->seed();
    $this->actingAs(tmUser('schooladmin1@example.com'));
    Filament::setCurrentPanel('school-admin');

    $hia = tmSchool('HIA-2026');
    $osc = tmSchool('OSC-2026');
    $teacher = tmUser('teacher5@example.com')->teacher;

    Livewire::test(SchoolListTeachers::class)
        ->callTableAction('removeFromSchool', $teacher, ['schools' => [$osc->getKey()]])
        ->assertHasFormErrors(['schools']);

    $teacher->refresh();

    expect($teacher->schools()->where('schools.id', $hia->getKey())->exists())->toBeTrue()
        ->and($teacher->schools()->where('schools.id', $osc->getKey())->exists())->toBeTrue();
});

test('a school admin editing a teacher leaves other schools and classes untouched', function () {
    $this->seed();
    $this->actingAs(tmUser('schooladmin1@example.com'));
    Filament::setCurrentPanel('school-admin');

    $hia = tmSchool('HIA-2026');
    $osc = tmSchool('OSC-2026');
    $teacher = tmUser('teacher5@example.com')->teacher;

    $hiaClass = tmClass($hia, '11-A ICT & Computing');
    $oscClass = tmClass($osc, '9-B Computer Studies');

    Livewire::test(SchoolEditTeacher::class, ['record' => $teacher->getKey()])
        ->assertActionExists('removeFromSchool')
        ->mountAction('removeFromSchool')
        ->assertActionMounted('removeFromSchool');

    Livewire::test(SchoolEditTeacher::class, ['record' => $teacher->getKey()])
        ->fillForm([
            'schools' => [],
            'classes' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $teacher->refresh();

    expect($teacher->schools()->where('schools.id', $hia->getKey())->exists())->toBeFalse()
        ->and($teacher->schools()->where('schools.id', $osc->getKey())->exists())->toBeTrue()
        ->and($teacher->classes()->where('learning_classes.id', $hiaClass->getKey())->exists())->toBeFalse()
        ->and($teacher->classes()->where('learning_classes.id', $oscClass->getKey())->exists())->toBeTrue();
});

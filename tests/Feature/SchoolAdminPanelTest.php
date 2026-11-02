<?php

use App\Filament\SchoolAdmin\Pages\Auth\Login as SchoolAdminLogin;
use App\Filament\SchoolAdmin\Resources\Assignments\Pages\ListAssignments;
use App\Filament\SchoolAdmin\Resources\Grades\Pages\CreateGrade;
use App\Filament\SchoolAdmin\Resources\Grades\Pages\ListGrades;
use App\Filament\SchoolAdmin\Resources\LearningClasses\Pages\CreateLearningClass;
use App\Filament\SchoolAdmin\Resources\LearningClasses\Pages\ListLearningClasses;
use App\Filament\SchoolAdmin\Resources\Lessons\Pages\ListLessons;
use App\Filament\SchoolAdmin\Resources\Quizzes\Pages\ListQuizzes;
use App\Filament\SchoolAdmin\Resources\Schools\Pages\ListSchools;
use App\Filament\SchoolAdmin\Resources\Schools\SchoolResource;
use App\Filament\SchoolAdmin\Resources\Students\Pages\CreateStudent;
use App\Filament\SchoolAdmin\Resources\Students\Pages\EditStudent;
use App\Filament\SchoolAdmin\Resources\Students\Pages\ListStudents;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\SchoolAdmin\Resources\Teachers\Pages\ListTeachers;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function spSchool(string $code): School
{
    return School::where('code', $code)->firstOrFail();
}

function spSchoolAdmin(): User
{
    return User::where('email', 'schooladmin1@example.com')->firstOrFail();
}

function spUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function spGrade(School $school, string $name): Grade
{
    return Grade::where('school_id', $school->getKey())->where('name', $name)->firstOrFail();
}

function spClass(School $school, string $name): LearningClass
{
    return LearningClass::whereHas('grade', fn ($q) => $q->where('school_id', $school->getKey()))
        ->where('name', $name)
        ->firstOrFail();
}

function spLessonIn(School $school): Lesson
{
    return Lesson::whereHas('learningClass.grade', fn ($q) => $q->where('school_id', $school->getKey()))->firstOrFail();
}

function spAssignmentIn(School $school): Assignment
{
    return Assignment::whereHas('learningClass.grade', fn ($q) => $q->where('school_id', $school->getKey()))->firstOrFail();
}

function spQuizIn(School $school): Quiz
{
    return Quiz::whereHas('learningClass.grade', fn ($q) => $q->where('school_id', $school->getKey()))->firstOrFail();
}

function spStudentIn(School $school): Student
{
    return Student::whereHas('enrollments', fn ($q) => $q->where('school_id', $school->getKey()))->firstOrFail();
}

test('school admin login page renders with school admin branding', function () {
    $this->seed();
    config(['app.name' => 'Student Platform']);

    $response = $this->get('/school-admin/login');

    $response->assertOk()
        ->assertSee('School Admin Portal')
        ->assertDontSee('Laravel');
});

test('school admin can sign in from the login page', function () {
    $this->seed();
    Filament::setCurrentPanel('school-admin');

    Livewire::test(SchoolAdminLogin::class)
        ->fillForm([
            'email' => 'schooladmin1@example.com',
            'password' => '12345678',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth()->check())->toBeTrue();
});

test('school admin cannot sign in with wrong password', function () {
    $this->seed();
    Filament::setCurrentPanel('school-admin');

    Livewire::test(SchoolAdminLogin::class)
        ->fillForm([
            'email' => 'schooladmin1@example.com',
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    expect(auth()->check())->toBeFalse();
});

test('school admin is forbidden from the super admin panel', function () {
    $this->seed();

    $this->actingAs(spSchoolAdmin())
        ->get('/admin')
        ->assertForbidden();
});

test('non-school-admin roles cannot access the school admin panel', function () {
    $this->seed();

    $this->actingAs(spUser('admin1@example.com'))->get('/school-admin')->assertForbidden();
    $this->actingAs(spUser('teacher1@example.com'))->get('/school-admin')->assertForbidden();
    $this->actingAs(spUser('student1@example.com'))->get('/school-admin')->assertForbidden();
});

test('school admin can render the dashboard', function () {
    $this->seed();

    $this->actingAs(spSchoolAdmin())
        ->get('/school-admin')
        ->assertRedirect('/school-admin/school-admin-dashboard');

    $this->actingAs(spSchoolAdmin())
        ->get('/school-admin/school-admin-dashboard')
        ->assertOk()
        ->assertSee('Horizon International Academy')
        ->assertDontSee('Oakridge STEM College');
});

test('school admin only sees their assigned schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(ListSchools::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([spSchool('HIA-2026')])
        ->assertCanNotSeeTableRecords([spSchool('OSC-2026')]);
});

test('school admins cannot create or delete schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());

    $this->get('/school-admin/schools/create')
        ->assertNotFound();

    expect(SchoolResource::canCreate())->toBeFalse()
        ->and(SchoolResource::canDelete(spSchool('HIA-2026')))->toBeFalse();
});

test('grades are scoped to the assigned schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(ListGrades::class)
        ->assertSuccessful()
        ->assertSee('Grade 10')
        ->assertSee('Horizon International Academy')
        ->assertDontSee('Grade 8')
        ->assertDontSee('Oakridge STEM College');
});

test('learning classes are scoped to the assigned schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(ListLearningClasses::class)
        ->assertSuccessful()
        ->assertSee('10-B Science & Physics')
        ->assertDontSee('8-A General Science');
});

test('teachers are scoped to the assigned schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(ListTeachers::class)
        ->assertSuccessful()
        ->assertSee('Dr. Robert Langdon')
        ->assertDontSee('David Attenborough');
});

test('students are scoped to the assigned schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(ListStudents::class)
        ->assertSuccessful()
        ->assertSee('Liam Ethan Miller')
        ->assertDontSee('Emma Charlotte Taylor');
});

test('lessons are scoped to the assigned schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(ListLessons::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([spLessonIn(spSchool('HIA-2026'))])
        ->assertCanNotSeeTableRecords([spLessonIn(spSchool('OSC-2026'))]);
});

test('assignments are scoped to the assigned schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(ListAssignments::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([spAssignmentIn(spSchool('HIA-2026'))])
        ->assertCanNotSeeTableRecords([spAssignmentIn(spSchool('OSC-2026'))]);
});

test('quizzes are scoped to the assigned schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(ListQuizzes::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([spQuizIn(spSchool('HIA-2026'))]);
});

test('lesson filters narrow records by class, teacher and publication status', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    $school = spSchool('HIA-2026');
    $lesson = spLessonIn($school);
    $otherClassLesson = Lesson::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->where('learning_class_id', '!=', $lesson->learning_class_id)
        ->firstOrFail();
    $otherTeacherLesson = Lesson::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->where('teacher_id', '!=', $lesson->teacher_id)
        ->firstOrFail();

    Livewire::test(ListLessons::class)
        ->filterTable('learning_class_id', $lesson->learning_class_id)
        ->assertCanSeeTableRecords([$lesson])
        ->assertCanNotSeeTableRecords([$otherClassLesson]);

    Livewire::test(ListLessons::class)
        ->filterTable('teacher_id', $lesson->teacher_id)
        ->assertCanSeeTableRecords([$lesson])
        ->assertCanNotSeeTableRecords([$otherTeacherLesson]);

    $lesson->update(['is_published' => false]);

    Livewire::test(ListLessons::class)
        ->filterTable('is_published', false)
        ->assertCanSeeTableRecords([$lesson])
        ->assertCanNotSeeTableRecords([$otherClassLesson]);

    Livewire::test(ListLessons::class)
        ->assertSuccessful()
        ->assertTableFilterExists('school_id')
        ->assertTableFilterExists('grade_id')
        ->assertTableFilterExists('learning_class_id')
        ->assertTableFilterExists('teacher_id')
        ->assertTableFilterExists('has_attachments')
        ->assertTableFilterExists('has_video');
});

test('assignment filters narrow records by class, late submissions and availability', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    $school = spSchool('HIA-2026');
    $assignment = spAssignmentIn($school);
    $otherClassAssignment = Assignment::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->where('learning_class_id', '!=', $assignment->learning_class_id)
        ->firstOrFail();
    $lateAssignment = Assignment::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->where('allow_late_submissions', true)
        ->firstOrFail();
    $nonLateAssignment = Assignment::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->where('allow_late_submissions', false)
        ->firstOrFail();

    Livewire::test(ListAssignments::class)
        ->filterTable('learning_class_id', $assignment->learning_class_id)
        ->assertCanSeeTableRecords([$assignment])
        ->assertCanNotSeeTableRecords([$otherClassAssignment]);

    Livewire::test(ListAssignments::class)
        ->filterTable('allow_late_submissions', true)
        ->assertCanSeeTableRecords([$lateAssignment])
        ->assertCanNotSeeTableRecords([$nonLateAssignment]);

    $expiredAssignment = Assignment::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->whereKeyNot($lateAssignment->getKey())
        ->firstOrFail();

    $expiredAssignment->update([
        'is_published' => true,
        'availability_type' => 'immediate',
        'start_at' => now()->subDays(10),
        'end_at' => now()->subDay(),
    ]);

    Livewire::test(ListAssignments::class)
        ->filterTable('availability_status', 'expired')
        ->assertCanSeeTableRecords([$expiredAssignment])
        ->assertCanNotSeeTableRecords([$lateAssignment]);

    Livewire::test(ListAssignments::class)
        ->assertSuccessful()
        ->assertTableFilterExists('school_id')
        ->assertTableFilterExists('grade_id')
        ->assertTableFilterExists('learning_class_id')
        ->assertTableFilterExists('teacher_id')
        ->assertTableFilterExists('deadline')
        ->assertTableFilterExists('has_submissions');
});

test('quiz filters narrow records by class, time limit and publication status', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    $school = spSchool('HIA-2026');
    $quiz = spQuizIn($school);
    $otherClassQuiz = Quiz::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->where('learning_class_id', '!=', $quiz->learning_class_id)
        ->firstOrFail();
    $timedQuiz = Quiz::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->whereNotNull('time_limit_minutes')
        ->firstOrFail();
    $untimedQuiz = Quiz::query()
        ->whereHas('learningClass.grade', fn ($query) => $query->where('school_id', $school->getKey()))
        ->whereKeyNot($timedQuiz->getKey())
        ->firstOrFail();

    $untimedQuiz->update(['time_limit_minutes' => null]);

    Livewire::test(ListQuizzes::class)
        ->filterTable('learning_class_id', $quiz->learning_class_id)
        ->assertCanSeeTableRecords([$quiz])
        ->assertCanNotSeeTableRecords([$otherClassQuiz]);

    Livewire::test(ListQuizzes::class)
        ->filterTable('has_time_limit', true)
        ->assertCanSeeTableRecords([$timedQuiz])
        ->assertCanNotSeeTableRecords([$untimedQuiz]);

    $timedQuiz->update(['is_published' => false]);

    Livewire::test(ListQuizzes::class)
        ->filterTable('is_published', false)
        ->assertCanSeeTableRecords([$timedQuiz])
        ->assertCanNotSeeTableRecords([$otherClassQuiz]);

    Livewire::test(ListQuizzes::class)
        ->assertSuccessful()
        ->assertTableFilterExists('school_id')
        ->assertTableFilterExists('grade_id')
        ->assertTableFilterExists('learning_class_id')
        ->assertTableFilterExists('teacher_id')
        ->assertTableFilterExists('availability_status')
        ->assertTableFilterExists('has_attempts');
});

test('records in other schools are not accessible', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());

    $foreignGrade = spGrade(spSchool('OSC-2026'), 'Grade 8');
    $foreignClass = spClass(spSchool('OSC-2026'), '8-A General Science');
    $foreignStudent = spStudentIn(spSchool('OSC-2026'));

    $this->get("/school-admin/grades/{$foreignGrade->getKey()}")->assertNotFound();
    $this->get("/school-admin/learning-classes/{$foreignClass->getKey()}/edit")->assertNotFound();
    $this->get("/school-admin/students/{$foreignStudent->getKey()}/edit")->assertNotFound();
});

test('a grade cannot be created for a school outside the admin schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(CreateGrade::class)
        ->fillForm([
            'school_id' => spSchool('OSC-2026')->getKey(),
            'name' => 'Sneaky Grade',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['school_id']);

    expect(Grade::where('name', 'Sneaky Grade')->exists())->toBeFalse();
});

test('a learning class cannot be created for a grade outside the admin schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(CreateLearningClass::class)
        ->fillForm([
            'name' => 'Sneaky Class',
            'grade_id' => spGrade(spSchool('OSC-2026'), 'Grade 8')->getKey(),
            'medium' => 'English',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['grade_id']);

    expect(LearningClass::where('name', 'Sneaky Class')->exists())->toBeFalse();
});

test('a teacher cannot be created for a school outside the admin schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(CreateTeacher::class)
        ->fillForm([
            'name' => 'Sneaky Teacher',
            'email' => 'sneaky.teacher@example.com',
            'employee_no' => 'EMP-T-9999',
            'schools' => [spSchool('OSC-2026')->getKey()],
        ])
        ->call('create')
        ->assertHasFormErrors(['schools']);

    expect(User::where('email', 'sneaky.teacher@example.com')->exists())->toBeFalse();
});

test('school admin can create a new teacher account by email', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(CreateTeacher::class)
        ->fillForm([
            'name' => 'Grace Hopper',
            'email' => 'grace.hopper@example.com',
            'employee_no' => 'EMP-T-2001',
            'schools' => [spSchool('HIA-2026')->getKey()],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'grace.hopper@example.com')->firstOrFail();

    expect($user->hasRole('teacher'))->toBeTrue()
        ->and($user->must_change_password)->toBeTruthy()
        ->and($user->teacher)->not->toBeNull()
        ->and($user->teacher->schools()->pluck('schools.id')->all())->toBe([spSchool('HIA-2026')->getKey()]);
});

test('creating a teacher reuses an existing account without breaking other roles', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(CreateTeacher::class)
        ->fillForm([
            'name' => 'Emma Charlotte Taylor',
            'email' => 'student6@example.com',
            'employee_no' => 'EMP-T-2002',
            'schools' => [spSchool('HIA-2026')->getKey()],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'student6@example.com')->firstOrFail();

    expect($user->hasRole('student'))->toBeTrue()
        ->and($user->hasRole('teacher'))->toBeTrue()
        ->and($user->student)->not->toBeNull()
        ->and($user->teacher)->not->toBeNull()
        ->and($user->teacher->schools()->pluck('schools.id')->all())->toBe([spSchool('HIA-2026')->getKey()]);
});

test('school admin can create a new student account with an enrollment', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    Livewire::test(CreateStudent::class)
        ->fillForm([
            'name' => 'Test Student',
            'email' => 'test.student@example.com',
            'admission_no' => 'ADM-TEST-001',
            'assign_school' => true,
            'schools' => [spSchool('HIA-2026')->getKey()],
            'grades' => [spGrade(spSchool('HIA-2026'), 'Grade 10')->getKey()],
            'classes' => [spClass(spSchool('HIA-2026'), '10-A Mathematics')->getKey()],
            'academic_year' => 2026,
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'test.student@example.com')->firstOrFail();
    $student = $user->student;

    expect($user->hasRole('student'))->toBeTrue()
        ->and($user->must_change_password)->toBeTruthy()
        ->and($student)->not->toBeNull()
        ->and($student->enrollments()->where('school_id', spSchool('HIA-2026')->getKey())->exists())->toBeTrue()
        ->and($student->classes()->where('learning_classes.id', spClass(spSchool('HIA-2026'), '10-A Mathematics')->getKey())->exists())->toBeTrue();
});

test('editing a student only manages enrollments in the admin schools', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());
    Filament::setCurrentPanel('school-admin');

    $school1 = spSchool('HIA-2026');
    $school2 = spSchool('OSC-2026');
    $student = spStudentIn($school1);

    $outOfScope = StudentEnrollment::create([
        'student_id' => $student->getKey(),
        'school_id' => $school2->getKey(),
        'grade_id' => spGrade($school2, 'Grade 8')->getKey(),
        'academic_year' => 2026,
        'status' => 'active',
    ]);

    Livewire::test(EditStudent::class, ['record' => $student->getKey()])
        ->fillForm([
            'name' => $student->user->name,
            'email' => $student->user->email,
            'admission_no' => $student->admission_no,
            'assign_school' => false,
            'schools' => [],
            'grades' => [],
            'classes' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $student->refresh();

    expect($student->enrollments()->where('school_id', $school1->getKey())->exists())->toBeFalse()
        ->and($student->enrollments()->where('school_id', $school2->getKey())->exists())->toBeTrue();
});

test('creating a quiz from an out of scope learning class link still loads safely', function () {
    $this->seed();
    $this->actingAs(spSchoolAdmin());

    $foreignClass = spClass(spSchool('OSC-2026'), '8-A General Science');
    $ownClass = spClass(spSchool('HIA-2026'), '10-A Mathematics');

    $this->get('/school-admin/quizzes/create?learningClassId='.$foreignClass->getKey())
        ->assertOk();

    $this->get('/school-admin/quizzes/create?learningClassId='.$ownClass->getKey())
        ->assertOk();
});

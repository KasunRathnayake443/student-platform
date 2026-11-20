<?php

use App\Filament\Resources\Students\Pages\CreateStudent as AdminCreateStudent;
use App\Filament\SchoolAdmin\Resources\Students\Pages\CreateStudent as SchoolAdminCreateStudent;
use App\Filament\Widgets\PlatformStatsOverviewWidget;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function introByUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function introByTeacher(string $employeeNo): Teacher
{
    return Teacher::where('employee_no', $employeeNo)->firstOrFail();
}

function introByStudent(string $email): Student
{
    return Student::whereHas('user', fn ($query) => $query->where('email', $email))->firstOrFail();
}

test('super admin can choose the teacher who introduced a student', function () {
    $this->seed();
    $admin = introByUser('admin1@example.com');
    $teacher = introByTeacher('EMP-T-1001');

    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminCreateStudent::class)
        ->fillForm([
            'name' => 'Introduced Student',
            'email' => 'introduced-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'admission_no' => 'ADM-INTRO-ADMIN',
            'introduced_by_teacher_id' => $teacher->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $student = Student::where('admission_no', 'ADM-INTRO-ADMIN')->firstOrFail();

    expect($student->introduced_by_teacher_id)->toBe($teacher->id)
        ->and($student->introducedByTeacher->is($teacher))->toBeTrue();
});

test('school admin can choose a teacher from their own school', function () {
    $this->seed();
    $admin = introByUser('schooladmin1@example.com');
    $teacher = introByTeacher('EMP-T-1001');

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($admin)
        ->test(SchoolAdminCreateStudent::class)
        ->fillForm([
            'name' => 'Introduced School Student',
            'email' => 'introduced-school@example.com',
            'admission_no' => 'ADM-INTRO-SCHOOL',
            'introduced_by_teacher_id' => $teacher->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $student = Student::where('admission_no', 'ADM-INTRO-SCHOOL')->firstOrFail();

    expect($student->introduced_by_teacher_id)->toBe($teacher->id);
});

test('school admin cannot choose a teacher from another school', function () {
    $this->seed();
    $admin = introByUser('schooladmin1@example.com');
    $teacher = introByTeacher('EMP-T-1003');

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($admin)
        ->test(SchoolAdminCreateStudent::class)
        ->fillForm([
            'name' => 'Wrong Teacher Student',
            'email' => 'introduced-wrong@example.com',
            'admission_no' => 'ADM-INTRO-WRONG',
            'introduced_by_teacher_id' => $teacher->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['introduced_by_teacher_id']);
});

test('introduced student counts appear in teacher profile and dashboards', function () {
    $this->seed();

    $teacher = introByTeacher('EMP-T-1001');
    $schoolOneStudent = introByStudent('student1@example.com');
    $schoolTwoStudent = introByStudent('student5@example.com');

    $schoolOneStudent->update(['introduced_by_teacher_id' => $teacher->id]);
    $schoolTwoStudent->update(['introduced_by_teacher_id' => $teacher->id]);

    $schoolId = $schoolOneStudent->enrollments()->value('school_id');

    expect($teacher->introducedStudentsCount())->toBe(2)
        ->and($teacher->introducedStudentsCount([$schoolId]))->toBe(1);

    $this->actingAs(introByUser('teacher1@example.com'))
        ->get('/teacher')
        ->assertOk()
        ->assertSee('Students Introduced');

    $this->actingAs(introByUser('teacher1@example.com'))
        ->get('/teacher/my-profile')
        ->assertOk()
        ->assertSee('Students Introduced');

    $admin = introByUser('admin1@example.com');

    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(PlatformStatsOverviewWidget::class)
        ->assertOk()
        ->assertSee('Introduced Students');

    $this->actingAs($admin)
        ->get('/admin/teachers')
        ->assertOk()
        ->assertSee('Students Introduced');

    $this->actingAs($admin)
        ->get('/admin/teachers/'.$teacher->id)
        ->assertOk()
        ->assertSee('Students Introduced');

    $schoolAdmin = introByUser('schooladmin1@example.com');

    Filament::setCurrentPanel('school-admin');

    $this->actingAs($schoolAdmin)
        ->get('/school-admin/school-admin-dashboard')
        ->assertOk()
        ->assertSee('Students Introduced');

    $this->actingAs($schoolAdmin)
        ->get('/school-admin/teachers')
        ->assertOk()
        ->assertSee('Students Introduced');

    $this->actingAs($schoolAdmin)
        ->get('/school-admin/teachers/'.$teacher->id)
        ->assertOk()
        ->assertSee('Students Introduced');
});

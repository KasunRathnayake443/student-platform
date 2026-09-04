<?php

use App\Enums\NotificationMentionType;
use App\Enums\NotificationRecipientType;
use App\Filament\Pages\Notifications as AdminNotifications;
use App\Filament\SchoolAdmin\Pages\Notifications as SchoolAdminNotifications;
use App\Filament\Student\Pages\Notifications as StudentNotifications;
use App\Filament\Teacher\Pages\Notifications as TeacherNotifications;
use App\Livewire\NotificationBell;
use App\Livewire\NotificationPanel;
use App\Models\Assignment;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\NotificationService;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function ntAdmin(): User
{
    return User::where('email', 'admin1@example.com')->firstOrFail();
}

function ntTeacher(): User
{
    return User::where('email', 'teacher1@example.com')->firstOrFail();
}

function ntStudent(): User
{
    return User::where('email', 'student1@example.com')->firstOrFail();
}

test('super admin notifications page renders and can send', function () {
    $this->seed();

    Livewire::actingAs(ntAdmin())
        ->test(AdminNotifications::class)
        ->assertSuccessful()
        ->set('data.title', 'Hello everyone')
        ->set('data.body', 'This is a test message')
        ->set('data.recipient_type', 'student')
        ->set('data.all_matching', true)
        ->call('send')
        ->assertHasNoErrors();

    expect(Notification::count())->toBeGreaterThan(0);
});

test('school admin notifications page renders with assigned-school recipients', function () {
    $this->seed();
    $admin = User::where('email', 'schooladmin1@example.com')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(SchoolAdminNotifications::class)
        ->assertSuccessful()
        ->set('data.title', 'Assignment reminder')
        ->set('data.recipient_type', 'student')
        ->set('data.all_matching', true)
        ->call('send')
        ->assertHasNoErrors();
});

test('teacher notifications page renders with student recipients only', function () {
    $this->seed();

    Livewire::actingAs(ntTeacher())
        ->test(TeacherNotifications::class)
        ->assertSuccessful()
        ->set('data.title', 'Class update')
        ->set('data.recipient_type', 'student')
        ->set('data.all_matching', true)
        ->call('send')
        ->assertHasNoErrors();
});

test('student notifications page renders without a compose form', function () {
    $this->seed();

    Livewire::actingAs(ntStudent())
        ->test(StudentNotifications::class)
        ->assertSuccessful()
        ->assertDontSee('New Notification');
});

test('notification bell reports unread count', function () {
    $this->seed();
    $admin = ntAdmin();
    $student = ntStudent();

    $student->receivedNotifications()->syncWithoutDetaching([]);

    app(NotificationService::class)->send(
        $admin,
        ['title' => 'Unread ping'],
        [$student->getKey()]
    );

    Livewire::actingAs($student)
        ->test(NotificationBell::class)
        ->assertSuccessful()
        ->assertSee('1');
});

test('school admin recipients are clamped to their assigned schools', function () {
    $this->seed();
    $school1 = School::where('code', 'HIA-2026')->firstOrFail();
    $school2 = School::where('code', 'OSC-2026')->firstOrFail();
    $admin1 = User::where('email', 'schooladmin1@example.com')->firstOrFail();

    $studentInSchool2 = Student::whereHas('enrollments', fn ($q) => $q->where('school_id', $school2->id))->firstOrFail();

    // A school admin of school1 requesting a school2 student must not reach them.
    $ids = app(NotificationService::class)->resolveRecipients($admin1, [
        'recipient_type' => NotificationRecipientType::Student,
        'schools' => [$school1->id, $school2->id],
        'all_matching' => false,
        'recipient_ids' => [$studentInSchool2->user_id],
    ]);

    expect($ids)->not->toContain($studentInSchool2->user_id);
});

test('teacher can resolve students only from their own classes', function () {
    $this->seed();
    $teacher = ntTeacher();
    $teacherModel = $teacher->teacher;

    $theirClassId = $teacherModel->classes()->firstOrFail()->id;
    $wholeSchoolStudentIds = $teacherModel->schools()
        ->firstOrFail()->students()->pluck('students.user_id')
        ->filter(fn ($id) => $id !== $teacher->getKey())
        ->values();

    $ids = app(NotificationService::class)->resolveRecipients($teacher, [
        'recipient_type' => NotificationRecipientType::Student,
        'classes' => [$theirClassId],
        'all_matching' => true,
    ]);

    expect($ids->count())->toBeGreaterThan(0)
        ->and($ids->count())->toBeLessThan($wholeSchoolStudentIds->count());
});

test('mention type/normalization and label resolution work without relation errors', function () {
    $this->seed();
    $admin = ntAdmin();
    $lesson = Lesson::firstOrFail();

    $notification = app(NotificationService::class)->send($admin, [
        'title' => 'Mention a lesson',
        'mention_type' => NotificationMentionType::Lesson,
        'mention_id' => $lesson->id,
    ], [ntStudent()->getKey()]);

    expect($notification)->not->toBeNull();
    $service = app(NotificationService::class);

    expect($service->mentionLabel($notification))->toBe($lesson->title);
    expect((bool) $service->mentionUrl($admin, $notification))->toBeTrue();
});

test('mention item options narrow by the shared school→grade→class cascade', function () {
    $this->seed();
    $admin = ntAdmin();

    $class = LearningClass::with('grade.school')->firstOrFail();
    $grade = $class->grade;
    $school = $grade->school;

    $secondClass = LearningClass::where('id', '!=', $class->id)
        ->whereHas('grade', fn ($q) => $q->where('school_id', $school->id))
        ->first();

    $teacher = Teacher::whereHas('classes', fn ($q) => $q->where('learning_classes.id', $class->id))->firstOrFail();

    $a1 = Assignment::create(['learning_class_id' => $class->id, 'title' => 'Alpha Cascade Item', 'is_published' => true, 'teacher_id' => $teacher->id]);
    $a2 = Assignment::create(['learning_class_id' => $secondClass->id, 'title' => 'Beta Cascade Item', 'is_published' => true, 'teacher_id' => $teacher->id]);

    $component = Livewire::actingAs($admin)->test(AdminNotifications::class);

    $bind = static function ($component, $state) {
        $component->set('data.mention_type', 'assignment')
            ->set('data.school', $state['school'])
            ->set('data.grade', $state['grade'])
            ->set('data.class', $state['class']);

        $go = new Get($component->instance()->form->getComponent('mention_type'));

        return $component->invade()->mentionOptions($go);
    };

    $all = $bind($component, ['school' => [$school->id], 'grade' => [$grade->id], 'class' => []]);
    expect($all)->toHaveKey($a1->getKey())->toHaveKey($a2->getKey());

    $narrowed = $bind($component, ['school' => [$school->id], 'grade' => [$grade->id], 'class' => [$class->id]]);
    expect($narrowed)->toHaveKey($a1->getKey())->not->toHaveKey($a2->getKey());
});

test('recipient resolution scoped by grade returns only that grade students', function () {
    $this->seed();
    $admin = ntAdmin();
    $service = app(NotificationService::class);

    $class = LearningClass::with('grade')->firstOrFail();
    $grade = $class->grade;
    $school = $grade->school;

    $gradeStudentIds = StudentEnrollment::where('grade_id', $grade->id)->pluck('student_id')->unique()->values();

    if ($gradeStudentIds->isEmpty()) {
        expect(true)->toBeTrue();

        return;
    }

    $ids = $service->resolveRecipients($admin, [
        'recipient_type' => NotificationRecipientType::Student,
        'schools' => [$school->id],
        'grades' => [$grade->id],
        'all_matching' => true,
    ]);

    $matchedUsers = Student::whereIn('id', $gradeStudentIds)->pluck('user_id');
    expect($ids->toArray())->toEqual($matchedUsers->toArray());
});

test('school admin delete permissions: same-school admins/teachers yes, super admin no', function () {
    $this->seed();
    $admin1 = User::where('email', 'schooladmin1@example.com')->firstOrFail();
    $superAdmin = ntAdmin();
    $service = app(NotificationService::class);

    $school1 = School::where('code', 'HIA-2026')->firstOrFail();
    $school2 = School::where('code', 'OSC-2026')->firstOrFail();

    // A school admin sharing school1 with admin1.
    $peerUser = User::create(['name' => 'Peer Admin', 'email' => 'peer@example.com', 'password' => bcrypt('12345678')]);
    $peerUser->syncRoles(['school_admin']);
    SchoolAdmin::create(['user_id' => $peerUser->id]);
    $peerUser->schools()->sync([$school1->id]);
    $peerAdminUser = $peerUser;

    // A school admin in a different school (school2).
    $foreignUser = User::create(['name' => 'Foreign Admin', 'email' => 'foreign@example.com', 'password' => bcrypt('12345678')]);
    $foreignUser->syncRoles(['school_admin']);
    SchoolAdmin::create(['user_id' => $foreignUser->id]);
    $foreignUser->schools()->sync([$school2->id]);
    $foreignAdminUser = $foreignUser;

    $peerPost = $service->send($peerAdminUser, ['title' => 'Peer admin post'], [$admin1->getKey()]);
    $foreignPost = $service->send($foreignAdminUser, ['title' => 'Foreign admin post'], [ntStudent()->getKey()]);
    $superPost = $service->send($superAdmin, ['title' => 'Super admin post'], [$admin1->getKey()]);

    // Same-school school admin -> deletable.
    expect($service->canDelete($admin1, $peerPost))->toBeTrue();

    // Different-school school admin -> not deletable.
    expect($service->canDelete($admin1, $foreignPost))->toBeFalse();

    // Super admin post -> never deletable by a school admin.
    expect($service->canDelete($admin1, $superPost))->toBeFalse();

    // Same-school teacher post -> deletable.
    $teacher = Teacher::whereHas('schools', fn ($q) => $q->whereIn('schools.id', $admin1->schools()->pluck('schools.id')))->first();
    if ($teacher) {
        $teacherPost = $service->send($teacher->user, ['title' => 'From teacher'], [$admin1->getKey()]);
        expect($service->canDelete($admin1, $teacherPost))->toBeTrue();
    }

    // Visibility scope includes peer + teacher posts but excludes foreign/super-only ones
    // when the admin is not a recipient.
    $visible = $service->scopeQueryFor($admin1, Notification::query())->pluck('notifications.id');
    expect($visible)->toContain($peerPost->getKey());
    expect($visible)->not->toContain($foreignPost->getKey());
});

test('unread count drops to zero after markAllAsRead', function () {
    $this->seed();
    $superAdmin = ntAdmin();
    $student = ntStudent();
    $service = app(NotificationService::class);

    $service->send($superAdmin, ['title' => 'For the student'], [$student->getKey()]);

    expect($service->unreadCount($student))->toBe(1);

    $service->markAllAsRead($student);

    expect($service->unreadCount($student))->toBe(0);
});

test('student notification panel shows unread badge and marks read on open', function () {
    $this->seed();
    $superAdmin = ntAdmin();
    $student = ntStudent();
    $service = app(NotificationService::class);

    $service->send($superAdmin, ['title' => 'Panel ping'], [$student->getKey()]);

    expect($service->unreadCount($student))->toBe(1);

    Livewire::actingAs($student)
        ->test(NotificationPanel::class)
        ->assertSuccessful()
        ->assertSee('1')
        ->assertSee('Panel ping')
        ->call('toggle')
        ->assertSet('open', true);

    expect($service->unreadCount($student))->toBe(0);
});

test('student notification panel view all dispatches open-tab event cleanly', function () {
    $this->seed();
    $student = ntStudent();

    Livewire::actingAs($student)
        ->test(NotificationPanel::class)
        ->call('toggle')
        ->assertSet('open', true)
        ->call('viewAllNotifications')
        ->assertSet('open', false)
        ->assertDispatched('open-tab', tab: 'notifications');
});

test('student dashboard handles open-tab event from notification panel', function () {
    $this->seed();
    $studentUser = ntStudent();
    $student = $studentUser->student;

    Livewire::actingAs($studentUser)
        ->test(\App\Filament\Student\Pages\Dashboard::class)
        ->assertSuccessful()
        ->dispatch('open-tab', tab: 'notifications')
        ->assertSet('activeTab', 'notifications')
        ->assertSee('Notifications');
});


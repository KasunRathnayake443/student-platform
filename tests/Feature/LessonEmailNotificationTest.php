<?php

use App\Filament\Resources\Lessons\Pages\CreateLesson as AdminCreateLesson;
use App\Filament\Resources\Lessons\Pages\EditLesson as AdminEditLesson;
use App\Filament\SchoolAdmin\Resources\Lessons\Pages\CreateLesson as SchoolAdminCreateLesson;
use App\Filament\SchoolAdmin\Resources\Lessons\Pages\EditLesson as SchoolAdminEditLesson;
use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\LessonsRelationManager;
use App\Filament\Teacher\Resources\Lessons\Pages\EditLesson as TeacherEditLesson;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Services\SchoolActivityNotification;
use App\Services\SchoolEmailNotificationService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function lessonEmailSchool(): School
{
    return School::where('code', 'HIA-2026')->firstOrFail();
}

function lessonEmailClass(): LearningClass
{
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function lessonEmailUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function lessonEmailConfigureSchool(): School
{
    $school = lessonEmailSchool();
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    return $school->fresh();
}

function lessonEmailCreate(string $title, bool $published, bool $emailSent = false): Lesson
{
    $class = lessonEmailClass();
    $teacher = Teacher::whereHas('classes', fn ($query) => $query->whereKey($class->getKey()))->firstOrFail();

    return Lesson::create([
        'learning_class_id' => $class->getKey(),
        'teacher_id' => $teacher->getKey(),
        'title' => $title,
        'is_published' => $published,
        'email_sent' => $emailSent,
    ]);
}

test('published teacher lesson creation sends once and marks the lesson', function () {
    $this->seed();
    lessonEmailConfigureSchool();
    $teacher = lessonEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(LessonsRelationManager::class, [
            'ownerRecord' => lessonEmailClass(),
            'pageClass' => ViewLearningClass::class,
        ])
        ->callTableAction('createLesson', data: [
            'title' => 'Published Email Lesson',
            'is_published' => true,
            'sort_order' => 1,
        ]);

    $lesson = Lesson::where('title', 'Published Email Lesson')->firstOrFail();

    expect($lesson->email_sent)->toBeTrue();

    Notification::assertSentTo(
        [lessonEmailUser('student1@example.com'), lessonEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('draft teacher lesson creation does not send and remains unsent', function () {
    $this->seed();
    lessonEmailConfigureSchool();
    $teacher = lessonEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(LessonsRelationManager::class, [
            'ownerRecord' => lessonEmailClass(),
            'pageClass' => ViewLearningClass::class,
        ])
        ->callTableAction('createLesson', data: [
            'title' => 'Draft Email Lesson',
            'is_published' => false,
            'sort_order' => 1,
        ]);

    $lesson = Lesson::where('title', 'Draft Email Lesson')->firstOrFail();

    expect($lesson->email_sent)->toBeFalse();
    Notification::assertNothingSent();
});

test('admin and school admin create pages apply the lesson email rules', function () {
    $this->seed();
    lessonEmailConfigureSchool();
    $class = lessonEmailClass();
    $teacher = Teacher::whereHas('classes', fn ($query) => $query->whereKey($class->getKey()))->firstOrFail();
    $admin = lessonEmailUser('admin1@example.com');
    $schoolAdmin = lessonEmailUser('schooladmin1@example.com');
    Notification::fake();

    Livewire::actingAs($admin)
        ->test(AdminCreateLesson::class)
        ->fillForm([
            'learning_class_id' => $class->getKey(),
            'teacher_id' => $teacher->getKey(),
            'title' => 'Admin Created Published Lesson',
            'is_published' => true,
            'sort_order' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminCreateLesson::class)
        ->fillForm([
            'learning_class_id' => $class->getKey(),
            'teacher_id' => $teacher->getKey(),
            'title' => 'School Admin Created Published Lesson',
            'is_published' => true,
            'sort_order' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Lesson::where('title', 'Admin Created Published Lesson')->firstOrFail()->email_sent)->toBeTrue()
        ->and(Lesson::where('title', 'School Admin Created Published Lesson')->firstOrFail()->email_sent)->toBeTrue();

    Notification::assertCount(4);
});

test('standard edit publishes an unsent lesson and sends once', function () {
    $this->seed();
    lessonEmailConfigureSchool();
    $lesson = lessonEmailCreate('Draft To Publish', false);
    $teacher = lessonEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditLesson::class, ['record' => $lesson->getKey()])
        ->fillForm([
            'title' => 'Published By Edit',
            'is_published' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($lesson->fresh())
        ->title->toBe('Published By Edit')
        ->email_sent->toBeTrue();

    Notification::assertSentTo(
        [lessonEmailUser('student1@example.com'), lessonEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('standard edit does not resend a previously sent lesson', function () {
    $this->seed();
    lessonEmailConfigureSchool();
    $lesson = lessonEmailCreate('Already Sent', true, true);
    $teacher = lessonEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditLesson::class, ['record' => $lesson->getKey()])
        ->fillForm(['title' => 'Already Sent Updated'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($lesson->fresh())
        ->title->toBe('Already Sent Updated')
        ->email_sent->toBeTrue();

    Notification::assertNothingSent();
});

test('unpublishing a sent lesson does not resend and keeps the sent flag', function () {
    $this->seed();
    lessonEmailConfigureSchool();
    $lesson = lessonEmailCreate('Published Then Hidden', true, true);
    $teacher = lessonEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditLesson::class, ['record' => $lesson->getKey()])
        ->fillForm(['is_published' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($lesson->fresh())
        ->is_published->toBeFalse()
        ->email_sent->toBeTrue();

    Notification::assertNothingSent();
});

test('manual submit and email action saves changes and resends students', function () {
    $this->seed();
    lessonEmailConfigureSchool();
    $lesson = lessonEmailCreate('Manual Resend', true, true);
    $teacher = lessonEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditLesson::class, ['record' => $lesson->getKey()])
        ->assertActionVisible('submitAndEmailStudents')
        ->fillForm(['title' => 'Manual Resend Updated'])
        ->callAction('submitAndEmailStudents')
        ->assertHasNoActionErrors();

    expect($lesson->fresh())
        ->title->toBe('Manual Resend Updated')
        ->email_sent->toBeTrue();

    Notification::assertSentTo(
        [lessonEmailUser('student1@example.com'), lessonEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('manual submit and email action is hidden until a lesson has been emailed', function () {
    $this->seed();
    $lesson = lessonEmailCreate('Not Emailed Yet', false);
    $teacher = lessonEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');

    Livewire::actingAs($teacher)
        ->test(TeacherEditLesson::class, ['record' => $lesson->getKey()])
        ->assertActionHidden('submitAndEmailStudents');
});

test('admin and school admin edit pages expose the manual lesson action', function () {
    $this->seed();
    $lesson = lessonEmailCreate('Admin Manual Resend', true, true);
    $admin = lessonEmailUser('admin1@example.com');
    $schoolAdmin = lessonEmailUser('schooladmin1@example.com');

    Livewire::actingAs($admin)
        ->test(AdminEditLesson::class, ['record' => $lesson->getKey()])
        ->assertActionVisible('submitAndEmailStudents');

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminEditLesson::class, ['record' => $lesson->getKey()])
        ->assertActionVisible('submitAndEmailStudents');
});

test('lesson edit pages place submit and manual email actions in the form actions', function () {
    $this->seed();
    $lesson = lessonEmailCreate('Action Placement', true, true);
    $teacher = lessonEmailUser('teacher1@example.com');
    $admin = lessonEmailUser('admin1@example.com');
    $schoolAdmin = lessonEmailUser('schooladmin1@example.com');

    Filament::setCurrentPanel('teacher');
    $teacherPage = Livewire::actingAs($teacher)
        ->test(TeacherEditLesson::class, ['record' => $lesson->getKey()])
        ->instance();

    Filament::setCurrentPanel('admin');
    $adminPage = Livewire::actingAs($admin)
        ->test(AdminEditLesson::class, ['record' => $lesson->getKey()])
        ->instance();

    Filament::setCurrentPanel('school-admin');
    $schoolAdminPage = Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminEditLesson::class, ['record' => $lesson->getKey()])
        ->instance();

    foreach ([$teacherPage, $adminPage, $schoolAdminPage] as $page) {
        $formActions = collect((fn (): array => $this->getFormActions())->call($page))
            ->mapWithKeys(fn (Action $action): array => [$action->getName() => $action->getLabel()]);

        $headerActions = collect((fn (): array => $this->getHeaderActions())->call($page))
            ->map(fn (Action $action): string => $action->getName())
            ->all();

        expect($formActions->get('save'))->toBe('Submit')
            ->and($formActions->get('submitAndEmailStudents'))->toBe('Submit & Email Students')
            ->and($headerActions)->not->toContain('submitAndEmailStudents');
    }
});

test('lesson emails are sent through the smtp settings of the school that owns the class', function () {
    $this->seed();
    $school = lessonEmailConfigureSchool();

    $otherSchool = School::create([
        'name' => 'Second Horizon School',
        'code' => 'HIA-2027',
        'email' => 'notifications@second.example',
        'smtp_host' => 'smtp.second.example',
        'smtp_password' => 'second-secret',
        'smtp_port' => 465,
        'smtp_encryption' => 'ssl',
        'is_active' => true,
    ]);

    $class = lessonEmailClass();

    expect($class->grade->school_id)->toBe($school->getKey());

    $lesson = lessonEmailCreate('Routed Lesson', true);
    Notification::fake();

    app(SchoolEmailNotificationService::class)->lessonCreated($lesson);

    Notification::assertSentTo(
        [lessonEmailUser('student1@example.com'), lessonEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => $notification->schoolId === $school->getKey()
    );
    Notification::assertCount(2);

    $student = lessonEmailUser('student1@example.com');

    $lessonMail = (new SchoolActivityNotification(
        $school->getKey(),
        'New lesson: Routed Lesson',
        'Your teacher has published a new lesson.',
    ))->toMail($student);

    $otherMail = (new SchoolActivityNotification(
        $otherSchool->getKey(),
        'New lesson: Routed Lesson',
        'Your teacher has published a new lesson.',
    ))->toMail($student);

    expect($lessonMail->mailer)->toStartWith("school-{$school->getKey()}-")
        ->and($otherMail->mailer)->toStartWith("school-{$otherSchool->getKey()}-")
        ->and($lessonMail->mailer)->not->toBe($otherMail->mailer)
        ->and($lessonMail->from)->toBe([$school->email, $school->name]);
});

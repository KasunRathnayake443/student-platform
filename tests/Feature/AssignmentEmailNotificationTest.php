<?php

use App\Filament\Resources\Assignments\Pages\CreateAssignment as AdminCreateAssignment;
use App\Filament\Resources\Assignments\Pages\EditAssignment as AdminEditAssignment;
use App\Filament\SchoolAdmin\Resources\Assignments\Pages\CreateAssignment as SchoolAdminCreateAssignment;
use App\Filament\SchoolAdmin\Resources\Assignments\Pages\EditAssignment as SchoolAdminEditAssignment;
use App\Filament\Teacher\Resources\Assignments\Pages\EditAssignment as TeacherEditAssignment;
use App\Filament\Teacher\Resources\LearningClasses\Pages\ViewLearningClass;
use App\Filament\Teacher\Resources\LearningClasses\RelationManagers\AssignmentsRelationManager;
use App\Models\Assignment;
use App\Models\LearningClass;
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

function assignmentEmailSchool(): School
{
    return School::where('code', 'HIA-2026')->firstOrFail();
}

function assignmentEmailClass(): LearningClass
{
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function assignmentEmailUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function assignmentEmailConfigureSchool(): School
{
    $school = assignmentEmailSchool();
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    return $school->fresh();
}

function assignmentEmailTeacher(): Teacher
{
    $class = assignmentEmailClass();

    return Teacher::whereHas('classes', fn ($query) => $query->whereKey($class->getKey()))
        ->firstOrFail();
}

function assignmentEmailCreate(
    string $title,
    bool $published,
    bool $emailSent = false,
    ?string $startAt = null,
    ?string $endAt = null,
): Assignment {
    $class = assignmentEmailClass();

    return Assignment::create([
        'learning_class_id' => $class->getKey(),
        'teacher_id' => assignmentEmailTeacher()->getKey(),
        'title' => $title,
        'max_score' => 100,
        'availability_type' => $startAt === null ? 'immediate' : 'scheduled',
        'start_at' => $startAt,
        'end_at' => $endAt ?? now()->addWeek()->format('Y-m-d H:i:s'),
        'allowed_submission_types' => ['text'],
        'is_published' => $published,
        'email_sent' => $emailSent,
    ]);
}

function assignmentEmailFormData(string $title, bool $published, ?string $endAt = null): array
{
    return [
        'title' => $title,
        'max_score' => 100,
        'available_immediately' => true,
        'end_at' => $endAt ?? now()->addWeek()->format('Y-m-d H:i:s'),
        'allowed_submission_types' => ['text'],
        'is_published' => $published,
    ];
}

test('published teacher assignment creation sends once and marks the assignment', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $teacher = assignmentEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(AssignmentsRelationManager::class, [
            'ownerRecord' => assignmentEmailClass(),
            'pageClass' => ViewLearningClass::class,
        ])
        ->callTableAction('createAssignment', data: [
            ...assignmentEmailFormData('Published Assignment Email', true),
            'teacher_ids' => [auth()->user()->teacher->getKey()],
        ]);

    $assignment = Assignment::where('title', 'Published Assignment Email')->firstOrFail();

    expect($assignment->email_sent)->toBeTrue();

    Notification::assertSentTo(
        [assignmentEmailUser('student1@example.com'), assignmentEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('draft teacher assignment creation does not send and remains unsent', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $teacher = assignmentEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(AssignmentsRelationManager::class, [
            'ownerRecord' => assignmentEmailClass(),
            'pageClass' => ViewLearningClass::class,
        ])
        ->callTableAction('createAssignment', data: [
            ...assignmentEmailFormData('Draft Assignment Email', false),
            'teacher_ids' => [auth()->user()->teacher->getKey()],
        ]);

    $assignment = Assignment::where('title', 'Draft Assignment Email')->firstOrFail();

    expect($assignment->email_sent)->toBeFalse();
    Notification::assertNothingSent();
});

test('admin and school admin create pages apply the assignment email rules', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $class = assignmentEmailClass();
    $teacher = assignmentEmailTeacher();
    $admin = assignmentEmailUser('admin1@example.com');
    $schoolAdmin = assignmentEmailUser('schooladmin1@example.com');
    Notification::fake();

    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminCreateAssignment::class)
        ->fillForm([
            ...assignmentEmailFormData('Admin Created Published Assignment', true),
            'learning_class_id' => $class->getKey(),
            'teacher_id' => $teacher->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminCreateAssignment::class)
        ->fillForm([
            ...assignmentEmailFormData('School Admin Created Published Assignment', true),
            'learning_class_id' => $class->getKey(),
            'teacher_id' => $teacher->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Assignment::where('title', 'Admin Created Published Assignment')->firstOrFail()->email_sent)->toBeTrue()
        ->and(Assignment::where('title', 'School Admin Created Published Assignment')->firstOrFail()->email_sent)->toBeTrue();

    Notification::assertCount(4);
});

test('standard edit publishes an unsent assignment and sends once', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $assignment = assignmentEmailCreate('Draft Assignment To Publish', false);
    $teacher = assignmentEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditAssignment::class, ['record' => $assignment->getKey()])
        ->fillForm([
            'title' => 'Published Assignment By Edit',
            'is_published' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($assignment->fresh())
        ->title->toBe('Published Assignment By Edit')
        ->email_sent->toBeTrue();

    Notification::assertSentTo(
        [assignmentEmailUser('student1@example.com'), assignmentEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('standard edit does not resend a previously sent assignment', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $assignment = assignmentEmailCreate('Already Sent Assignment', true, true);
    $teacher = assignmentEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditAssignment::class, ['record' => $assignment->getKey()])
        ->fillForm(['title' => 'Already Sent Assignment Updated'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($assignment->fresh())
        ->title->toBe('Already Sent Assignment Updated')
        ->email_sent->toBeTrue();

    Notification::assertNothingSent();
});

test('unpublishing a sent assignment does not resend and keeps the sent flag', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $assignment = assignmentEmailCreate('Published Then Hidden Assignment', true, true);
    $teacher = assignmentEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditAssignment::class, ['record' => $assignment->getKey()])
        ->fillForm(['is_published' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($assignment->fresh())
        ->is_published->toBeFalse()
        ->email_sent->toBeTrue();

    Notification::assertNothingSent();
});

test('manual submit and email action saves changes and resends students', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $assignment = assignmentEmailCreate('Manual Assignment Resend', true, true);
    $teacher = assignmentEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditAssignment::class, ['record' => $assignment->getKey()])
        ->assertActionVisible('submitAndEmailStudents')
        ->fillForm(['title' => 'Manual Assignment Resend Updated'])
        ->callAction('submitAndEmailStudents')
        ->assertHasNoActionErrors();

    expect($assignment->fresh())
        ->title->toBe('Manual Assignment Resend Updated')
        ->email_sent->toBeTrue();

    Notification::assertSentTo(
        [assignmentEmailUser('student1@example.com'), assignmentEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('manual submit and email action is hidden until an assignment has been emailed', function () {
    $this->seed();
    $assignment = assignmentEmailCreate('Not Emailed Assignment Yet', false);
    $teacher = assignmentEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');

    Livewire::actingAs($teacher)
        ->test(TeacherEditAssignment::class, ['record' => $assignment->getKey()])
        ->assertActionHidden('submitAndEmailStudents');
});

test('admin and school admin edit pages expose the manual assignment action', function () {
    $this->seed();
    $assignment = assignmentEmailCreate('Admin Manual Assignment Resend', true, true);
    $admin = assignmentEmailUser('admin1@example.com');
    $schoolAdmin = assignmentEmailUser('schooladmin1@example.com');

    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminEditAssignment::class, ['record' => $assignment->getKey()])
        ->assertActionVisible('submitAndEmailStudents');

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminEditAssignment::class, ['record' => $assignment->getKey()])
        ->assertActionVisible('submitAndEmailStudents');
});

test('assignment edit pages place submit and manual email actions in the form actions', function () {
    $this->seed();
    $assignment = assignmentEmailCreate('Assignment Action Placement', true, true);
    $teacher = assignmentEmailUser('teacher1@example.com');
    $admin = assignmentEmailUser('admin1@example.com');
    $schoolAdmin = assignmentEmailUser('schooladmin1@example.com');

    Filament::setCurrentPanel('teacher');
    $teacherPage = Livewire::actingAs($teacher)
        ->test(TeacherEditAssignment::class, ['record' => $assignment->getKey()])
        ->instance();

    Filament::setCurrentPanel('admin');
    $adminPage = Livewire::actingAs($admin)
        ->test(AdminEditAssignment::class, ['record' => $assignment->getKey()])
        ->instance();

    Filament::setCurrentPanel('school-admin');
    $schoolAdminPage = Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminEditAssignment::class, ['record' => $assignment->getKey()])
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

test('assignment emails show the start date and end date', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $startAt = now()->addDay()->startOfHour();
    $endAt = now()->addWeek()->startOfHour();
    $assignment = assignmentEmailCreate(
        'Dated Assignment',
        true,
        false,
        $startAt->format('Y-m-d H:i:s'),
        $endAt->format('Y-m-d H:i:s'),
    );

    Notification::fake();

    app(SchoolEmailNotificationService::class)->notifyPublishedAssignment($assignment);

    Notification::assertSentTo(
        assignmentEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => $notification->subject === 'New assignment: Dated Assignment'
            && in_array('Start date: '.$startAt->format('j M Y, g:i A'), $notification->lines, true)
            && in_array('End date: '.$endAt->format('j M Y, g:i A'), $notification->lines, true)
    );
});

test('assignment emails report immediate availability and no deadline', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $assignment = assignmentEmailCreate('Immediate Assignment', true, false, null, null);
    $assignment->update(['end_at' => null]);

    Notification::fake();

    app(SchoolEmailNotificationService::class)->notifyPublishedAssignment($assignment);

    Notification::assertSentTo(
        assignmentEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => in_array('Start date: Available immediately', $notification->lines, true)
            && in_array('End date: No deadline', $notification->lines, true)
    );
});

test('edited dates are included in the email sent on the first publish', function () {
    $this->seed();
    assignmentEmailConfigureSchool();
    $assignment = assignmentEmailCreate('Rescheduled Assignment', false);
    $teacher = assignmentEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    $newEndAt = now()->addDays(9)->startOfHour();
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditAssignment::class, ['record' => $assignment->getKey()])
        ->fillForm([
            'is_published' => true,
            'end_at' => $newEndAt->format('Y-m-d H:i:s'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($assignment->fresh())
        ->is_published->toBeTrue()
        ->email_sent->toBeTrue();

    Notification::assertSentTo(
        assignmentEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => in_array('End date: '.$newEndAt->format('j M Y, g:i A'), $notification->lines, true)
    );
});

test('assignment emails are sent through the smtp settings of the school that owns the class', function () {
    $this->seed();
    $school = assignmentEmailConfigureSchool();

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

    expect(assignmentEmailClass()->grade->school_id)->toBe($school->getKey());

    $assignment = assignmentEmailCreate('Routed Assignment', true);
    Notification::fake();

    app(SchoolEmailNotificationService::class)->notifyPublishedAssignment($assignment);

    Notification::assertSentTo(
        [assignmentEmailUser('student1@example.com'), assignmentEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => $notification->schoolId === $school->getKey()
    );
    Notification::assertCount(2);

    $student = assignmentEmailUser('student1@example.com');

    $assignmentMail = (new SchoolActivityNotification(
        $school->getKey(),
        'New assignment: Routed Assignment',
        'Your teacher has published a new assignment.',
    ))->toMail($student);

    $otherMail = (new SchoolActivityNotification(
        $otherSchool->getKey(),
        'New assignment: Routed Assignment',
        'Your teacher has published a new assignment.',
    ))->toMail($student);

    expect($assignmentMail->mailer)->toStartWith("school-{$school->getKey()}-")
        ->and($otherMail->mailer)->toStartWith("school-{$otherSchool->getKey()}-")
        ->and($assignmentMail->mailer)->not->toBe($otherMail->mailer)
        ->and($assignmentMail->from)->toBe([$school->email, $school->name]);
});

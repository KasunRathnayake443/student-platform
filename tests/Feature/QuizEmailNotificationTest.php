<?php

use App\Filament\Resources\Quizzes\Pages\CreateQuiz as AdminCreateQuiz;
use App\Filament\Resources\Quizzes\Pages\EditQuiz as AdminEditQuiz;
use App\Filament\SchoolAdmin\Resources\Quizzes\Pages\CreateQuiz as SchoolAdminCreateQuiz;
use App\Filament\SchoolAdmin\Resources\Quizzes\Pages\EditQuiz as SchoolAdminEditQuiz;
use App\Filament\Teacher\Resources\Quizzes\Pages\CreateQuiz as TeacherCreateQuiz;
use App\Filament\Teacher\Resources\Quizzes\Pages\EditQuiz as TeacherEditQuiz;
use App\Models\LearningClass;
use App\Models\Quiz;
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

function quizEmailSchool(): School
{
    return School::where('code', 'HIA-2026')->firstOrFail();
}

function quizEmailClass(): LearningClass
{
    return LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
}

function quizEmailUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function quizEmailConfigureSchool(): School
{
    $school = quizEmailSchool();
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    return $school->fresh();
}

function quizEmailTeacher(): Teacher
{
    $class = quizEmailClass();

    return Teacher::whereHas('classes', fn ($query) => $query->whereKey($class->getKey()))
        ->firstOrFail();
}

function quizEmailCreate(
    string $title,
    bool $published,
    bool $emailSent = false,
    ?string $startAt = null,
    ?string $endAt = null,
): Quiz {
    $class = quizEmailClass();

    $quiz = Quiz::create([
        'learning_class_id' => $class->getKey(),
        'teacher_id' => quizEmailTeacher()->getKey(),
        'title' => $title,
        'max_attempts' => 2,
        'passing_percentage' => 60,
        'total_points' => 0,
        'availability_type' => $startAt === null ? 'immediate' : 'scheduled',
        'start_at' => $startAt,
        'end_at' => $endAt ?? now()->addWeek()->format('Y-m-d H:i:s'),
        'is_published' => $published,
        'email_sent' => $emailSent,
    ]);

    $question = $quiz->questions()->create([
        'question_text' => 'What is 2 + 2?',
        'points' => 1,
        'sort_order' => 1,
    ]);

    $question->options()->create([
        'option_text' => '4',
        'is_correct' => true,
        'sort_order' => 1,
    ]);

    $question->options()->create([
        'option_text' => '5',
        'is_correct' => false,
        'sort_order' => 2,
    ]);

    $quiz->updateQuietly(['total_points' => 1]);

    return $quiz;
}

function quizEmailFormData(string $title, bool $published, ?string $endAt = null): array
{
    return [
        'title' => $title,
        'learning_class_id' => quizEmailClass()->getKey(),
        'teacher_ids' => [quizEmailTeacher()->getKey()],
        'max_attempts' => 1,
        'passing_percentage' => 50,
        'available_immediately' => true,
        'end_at' => $endAt ?? now()->addWeek()->format('Y-m-d H:i:s'),
        'is_published' => $published,
        'questions' => [
            [
                'question_text' => 'What is 2 + 2?',
                'points' => 1,
                'options' => [
                    ['option_text' => '4', 'is_correct' => true],
                    ['option_text' => '5', 'is_correct' => false],
                ],
            ],
        ],
    ];
}

test('published teacher quiz creation sends once and marks the quiz', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $teacher = quizEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherCreateQuiz::class)
        ->fillForm(quizEmailFormData('Published Quiz Email', true))
        ->call('create')
        ->assertHasNoFormErrors();

    $quiz = Quiz::where('title', 'Published Quiz Email')->firstOrFail();

    expect($quiz->email_sent)->toBeTrue();

    Notification::assertSentTo(
        [quizEmailUser('student1@example.com'), quizEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('draft teacher quiz creation does not send and remains unsent', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $teacher = quizEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherCreateQuiz::class)
        ->fillForm(quizEmailFormData('Draft Quiz Email', false))
        ->call('create')
        ->assertHasNoFormErrors();

    $quiz = Quiz::where('title', 'Draft Quiz Email')->firstOrFail();

    expect($quiz->email_sent)->toBeFalse();
    Notification::assertNothingSent();
});

test('admin and school admin create pages apply the quiz email rules', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $admin = quizEmailUser('admin1@example.com');
    $schoolAdmin = quizEmailUser('schooladmin1@example.com');
    Notification::fake();

    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminCreateQuiz::class)
        ->fillForm(quizEmailFormData('Admin Created Published Quiz', true))
        ->call('create')
        ->assertHasNoFormErrors();

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminCreateQuiz::class)
        ->fillForm(quizEmailFormData('School Admin Created Published Quiz', true))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Quiz::where('title', 'Admin Created Published Quiz')->firstOrFail()->email_sent)->toBeTrue()
        ->and(Quiz::where('title', 'School Admin Created Published Quiz')->firstOrFail()->email_sent)->toBeTrue();

    Notification::assertCount(4);
});

test('standard edit publishes an unsent quiz and sends once', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $quiz = quizEmailCreate('Draft Quiz To Publish', false);
    $teacher = quizEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->fillForm([
            'title' => 'Published Quiz By Edit',
            'is_published' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($quiz->fresh())
        ->title->toBe('Published Quiz By Edit')
        ->email_sent->toBeTrue();

    Notification::assertSentTo(
        [quizEmailUser('student1@example.com'), quizEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('standard edit does not resend a previously sent quiz', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $quiz = quizEmailCreate('Already Sent Quiz', true, true);
    $teacher = quizEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->fillForm(['title' => 'Already Sent Quiz Updated'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($quiz->fresh())
        ->title->toBe('Already Sent Quiz Updated')
        ->email_sent->toBeTrue();

    Notification::assertNothingSent();
});

test('unpublishing a sent quiz does not resend and keeps the sent flag', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $quiz = quizEmailCreate('Published Then Hidden Quiz', true, true);
    $teacher = quizEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->fillForm(['is_published' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($quiz->fresh())
        ->is_published->toBeFalse()
        ->email_sent->toBeTrue();

    Notification::assertNothingSent();
});

test('manual submit and email action saves changes and resends students', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $quiz = quizEmailCreate('Manual Quiz Resend', true, true);
    $teacher = quizEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->assertActionVisible('submitAndEmailStudents')
        ->fillForm(['title' => 'Manual Quiz Resend Updated'])
        ->callAction('submitAndEmailStudents')
        ->assertHasNoActionErrors();

    expect($quiz->fresh())
        ->title->toBe('Manual Quiz Resend Updated')
        ->email_sent->toBeTrue();

    Notification::assertSentTo(
        [quizEmailUser('student1@example.com'), quizEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
    );
    Notification::assertCount(2);
});

test('manual submit and email action is hidden until a quiz has been emailed', function () {
    $this->seed();
    $quiz = quizEmailCreate('Not Emailed Quiz Yet', false);
    $teacher = quizEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');

    Livewire::actingAs($teacher)
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->assertActionHidden('submitAndEmailStudents');
});

test('admin and school admin edit pages expose the manual quiz action', function () {
    $this->seed();
    $quiz = quizEmailCreate('Admin Manual Quiz Resend', true, true);
    $admin = quizEmailUser('admin1@example.com');
    $schoolAdmin = quizEmailUser('schooladmin1@example.com');

    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminEditQuiz::class, ['record' => $quiz->getKey()])
        ->assertActionVisible('submitAndEmailStudents');

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminEditQuiz::class, ['record' => $quiz->getKey()])
        ->assertActionVisible('submitAndEmailStudents');
});

test('quiz edit pages place submit and manual email actions in the form actions', function () {
    $this->seed();
    $quiz = quizEmailCreate('Quiz Action Placement', true, true);
    $teacher = quizEmailUser('teacher1@example.com');
    $admin = quizEmailUser('admin1@example.com');
    $schoolAdmin = quizEmailUser('schooladmin1@example.com');

    Filament::setCurrentPanel('teacher');
    $teacherPage = Livewire::actingAs($teacher)
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->instance();

    Filament::setCurrentPanel('admin');
    $adminPage = Livewire::actingAs($admin)
        ->test(AdminEditQuiz::class, ['record' => $quiz->getKey()])
        ->instance();

    Filament::setCurrentPanel('school-admin');
    $schoolAdminPage = Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminEditQuiz::class, ['record' => $quiz->getKey()])
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

test('quiz emails show the start date, end date and quiz rules', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $startAt = now()->addDay()->startOfHour();
    $endAt = now()->addWeek()->startOfHour();
    $quiz = quizEmailCreate(
        'Dated Quiz',
        true,
        false,
        $startAt->format('Y-m-d H:i:s'),
        $endAt->format('Y-m-d H:i:s'),
    );
    $quiz->update(['total_points' => 20, 'time_limit_minutes' => 30]);

    Notification::fake();

    app(SchoolEmailNotificationService::class)->notifyPublishedQuiz($quiz);

    Notification::assertSentTo(
        quizEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => $notification->subject === 'New quiz: Dated Quiz'
            && in_array('Start date: '.$startAt->format('j M Y, g:i A'), $notification->lines, true)
            && in_array('End date: '.$endAt->format('j M Y, g:i A'), $notification->lines, true)
            && in_array('Total points: 20', $notification->lines, true)
            && in_array('Time limit: 30 minutes', $notification->lines, true)
            && in_array('Attempts allowed: 2', $notification->lines, true)
            && in_array('Passing score: 60%', $notification->lines, true)
    );
});

test('quiz emails report immediate availability and no deadline', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $quiz = quizEmailCreate('Immediate Quiz', true, false, null, null);
    $quiz->update(['end_at' => null, 'time_limit_minutes' => null]);

    Notification::fake();

    app(SchoolEmailNotificationService::class)->notifyPublishedQuiz($quiz);

    Notification::assertSentTo(
        quizEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => in_array('Start date: Available immediately', $notification->lines, true)
            && in_array('End date: No deadline', $notification->lines, true)
            && ! in_array('Time limit: 30 minutes', $notification->lines, true)
    );
});

test('edited dates are included in the email sent on the first publish', function () {
    $this->seed();
    quizEmailConfigureSchool();
    $quiz = quizEmailCreate('Rescheduled Quiz', false);
    $teacher = quizEmailUser('teacher1@example.com');
    Filament::setCurrentPanel('teacher');
    $newEndAt = now()->addDays(9)->startOfHour();
    Notification::fake();

    Livewire::actingAs($teacher)
        ->test(TeacherEditQuiz::class, ['record' => $quiz->getKey()])
        ->fillForm([
            'is_published' => true,
            'end_at' => $newEndAt->format('Y-m-d H:i:s'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($quiz->fresh())
        ->is_published->toBeTrue()
        ->email_sent->toBeTrue();

    Notification::assertSentTo(
        quizEmailUser('student1@example.com'),
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => in_array('End date: '.$newEndAt->format('j M Y, g:i A'), $notification->lines, true)
    );
});

test('quiz emails are sent through the smtp settings of the school that owns the class', function () {
    $this->seed();
    $school = quizEmailConfigureSchool();

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

    expect(quizEmailClass()->grade->school_id)->toBe($school->getKey());

    $quiz = quizEmailCreate('Routed Quiz', true);
    Notification::fake();

    app(SchoolEmailNotificationService::class)->notifyPublishedQuiz($quiz);

    Notification::assertSentTo(
        [quizEmailUser('student1@example.com'), quizEmailUser('student2@example.com')],
        SchoolActivityNotification::class,
        fn (SchoolActivityNotification $notification): bool => $notification->schoolId === $school->getKey()
    );
    Notification::assertCount(2);

    $student = quizEmailUser('student1@example.com');

    $quizMail = (new SchoolActivityNotification(
        $school->getKey(),
        'New quiz: Routed Quiz',
        'Your teacher has published a new quiz.',
    ))->toMail($student);

    $otherMail = (new SchoolActivityNotification(
        $otherSchool->getKey(),
        'New quiz: Routed Quiz',
        'Your teacher has published a new quiz.',
    ))->toMail($student);

    expect($quizMail->mailer)->toStartWith("school-{$school->getKey()}-")
        ->and($otherMail->mailer)->toStartWith("school-{$otherSchool->getKey()}-")
        ->and($quizMail->mailer)->not->toBe($otherMail->mailer)
        ->and($quizMail->from)->toBe([$school->email, $school->name]);
});

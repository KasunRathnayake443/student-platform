<?php

namespace App\Filament\Notifications;

use App\Enums\NotificationMentionType;
use App\Enums\NotificationRecipientType;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\School;
use App\Models\User;
use App\Services\NotificationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as UiNotification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
abstract class NotificationsCenter extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|\UnitEnum|null $navigationGroup = 'Messaging';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();

        // Sender-capable panels have no per-item "mark as read" action, so opening
        // the inbox reads everything. Students keep explicit per-item read state.
        if ($this->canSend()) {
            $this->getNotificationService()->markAllAsRead(auth()->user());
        }
    }

    public function getNotificationService(): NotificationService
    {
        return app(NotificationService::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Sender configuration (override per panel)
    |--------------------------------------------------------------------------
    */

    /**
     * Recipient types the sender may target.
     *
     * @return array<string, string>
     */
    protected function recipientTypeOptions(): array
    {
        return NotificationRecipientType::options();
    }

    /**
     * School options for the compose form (already clamped to the sender's reach).
     *
     * @return array<int, string>
     */
    protected function schoolOptions(): array
    {
        return School::orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * Clamp the requested school ids to what this sender may actually reach.
     *
     * @param  array<int>  $requested
     * @return array<int>
     */
    protected function clampSchools(array $requested): array
    {
        if (auth()->user()->hasRole('super_admin')) {
            return $requested;
        }

        $allowed = auth()->user()->schools()->pluck('schools.id');

        return $requested ? $allowed->intersect($requested)->values()->all() : $allowed->all();
    }

    protected function canSend(): bool
    {
        return ! auth()->user()->hasRole('student');
    }

    /*
    |--------------------------------------------------------------------------
    | Compose form
    |--------------------------------------------------------------------------
    */

    public function form(Schema $schema): Schema
    {
        $components = [
            TextInput::make('title')->label('Title')->required()->maxLength(255),
            Textarea::make('body')->label('Message')->rows(3),
        ];

        if ($this->canSend()) {
            $components[] = Select::make('recipient_type')
                ->label('Recipient type')
                ->options($this->recipientTypeOptions())
                ->default(NotificationRecipientType::Student->value)
                ->live()
                ->required();
        }

        if ($this->canSend() && $this->usesFilterCascade()) {
            $components[] = Select::make('school')
                ->label('School')
                ->options($this->schoolOptions())
                ->multiple()
                ->searchable()
                ->live();

            $components[] = Select::make('grade')
                ->label('Grade')
                ->options(fn (Get $get) => $this->gradeOptions($get))
                ->multiple()
                ->searchable()
                ->live()
                ->hidden(fn (Get $get) => $this->cascadeLevelsHidden($get) > 0);

            $components[] = Select::make('class')
                ->label('Class')
                ->options(fn (Get $get) => $this->classOptions($get))
                ->multiple()
                ->searchable()
                ->live()
                ->hidden(fn (Get $get) => $this->cascadeLevelsHidden($get) > 1);
        }

        if ($this->canSend()) {
            $components[] = Checkbox::make('all_matching')
                ->label('Send to everyone matching the above')
                ->live();

            $components[] = Select::make('recipient_ids')
                ->label('Specific recipients (search by name or email)')
                ->options(fn (Get $get) => $this->recipientOptions($get))
                ->getSearchResultsUsing(fn (string $search, Get $get) => $this->searchRecipientOptions($get, $search))
                ->multiple()
                ->searchable()
                ->hidden(fn (Get $get) => (bool) $get('all_matching'));

            $components[] = Select::make('mention_type')
                ->label('Mention (optional)')
                ->options(NotificationMentionType::options())
                ->live()
                ->nullable()
                ->placeholder('No mention');

            $components[] = Select::make('mention_id')
                ->label('Mentioned item')
                ->options(fn (Get $get) => $this->mentionOptions($get))
                ->searchable()
                ->preload()
                ->hidden(fn (Get $get) => blank($get('mention_type')))
                ->nullable();
        }

        return $schema->components($components)->statePath('data');
    }

    protected function usesFilterCascade(): bool
    {
        return $this->canSend();
    }

    /**
     * Number of cascade levels (below School) that are irrelevant for the chosen
     * recipient type: grade + class are hidden for school_admin/super_admin recipients.
     */
    protected function cascadeLevelsHidden(Get $get): int
    {
        $type = NotificationRecipientType::tryFrom((string) $get('recipient_type'));

        if ($type === NotificationRecipientType::SchoolAdmin || $type === NotificationRecipientType::SuperAdmin) {
            return 2;
        }

        return 0;
    }

    /**
     * Grade options for the configured cascade, scoped to the selected schools.
     *
     * @return array<int, string>
     */
    protected function gradeOptions(Get $get): array
    {
        $schoolIds = $this->clampSchools(array_values(array_map('intval', $get('school') ?? [])));

        return Grade::query()
            ->when($schoolIds, fn ($q) => $q->whereIn('school_id', $schoolIds))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * Class options for the configured cascade, scoped to the selected grades.
     *
     * @return array<int, string>
     */
    protected function classOptions(Get $get): array
    {
        $gradeIds = $this->clampGrades(array_values(array_map('intval', $get('grade') ?? [])));

        return LearningClass::query()
            ->with('grade')
            ->when($gradeIds, fn ($q) => $q->whereIn('grade_id', $gradeIds))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (LearningClass $class) => [
                $class->getKey() => trim(($class->grade?->name ?? '').' · '.($class->name ?? ''), ' ·'),
            ])
            ->all();
    }

    /**
     * Clamp the requested grade ids to the sender's reachable schools.
     *
     * @param  array<int>  $requested
     * @return array<int>
     */
    protected function clampGrades(array $requested): array
    {
        if (auth()->user()->hasRole('super_admin')) {
            return $requested;
        }

        $schoolIds = auth()->user()->schools()->pluck('schools.id');
        $allowed = Grade::query()->whereIn('school_id', $schoolIds)->pluck('id');

        return $requested ? $allowed->intersect($requested)->values()->all() : $allowed->all();
    }

    protected function recipientOptions(Get $get): array
    {
        $type = NotificationRecipientType::tryFrom((string) $get('recipient_type'));

        if (! $type) {
            return [];
        }

        $query = $this->typeUserQuery($type, $get);

        return $query->limit(500)->get()
            ->mapWithKeys(fn (User $user) => [$user->getKey() => $user->name.' · '.($user->email ?? '')])
            ->all();
    }

    /**
     * Live search for individual recipients by name/email, scoped to the cascade.
     *
     * @return array<int, string>
     */
    protected function searchRecipientOptions(Get $get, string $search): array
    {
        $type = NotificationRecipientType::tryFrom((string) $get('recipient_type'));

        if (! $type) {
            return [];
        }

        $query = $this->typeUserQuery($type, $get)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });

        return $query->limit(50)->get()
            ->mapWithKeys(fn (User $user) => [$user->getKey() => $user->name.' · '.($user->email ?? '')])
            ->all();
    }

    protected function typeUserQuery(NotificationRecipientType $type, Get $get)
    {
        $sender = auth()->user();
        $schoolIds = array_values(array_map('intval', $get('school') ?? []));
        $gradeIds = array_values(array_map('intval', $get('grade') ?? []));
        $classIds = array_values(array_map('intval', $get('class') ?? []));

        $query = User::query();

        $relation = match ($type) {
            NotificationRecipientType::Student => 'student',
            NotificationRecipientType::Teacher => 'teacher',
            NotificationRecipientType::SchoolAdmin => 'schoolAdmin',
            default => null,
        };

        if ($type === NotificationRecipientType::SuperAdmin) {
            return $query->role('super_admin');
        }

        if (! $relation) {
            return $query;
        }

        $query->whereHas($relation);

        $schoolIds = $this->clampSchools($schoolIds);
        $gradeIds = $this->clampGrades($gradeIds);

        if ($type === NotificationRecipientType::Student) {
            if ($classIds) {
                $query->whereHas('student.classes', fn ($c) => $c->whereIn('learning_classes.id', $classIds));

                return $query;
            }

            if ($gradeIds) {
                $query->whereHas('student.enrollments', fn ($e) => $e->whereIn('grade_id', $gradeIds));

                return $query;
            }
        }

        if ($schoolIds) {
            $query->whereHas($relation.'.schools', fn ($s) => $s->whereIn('schools.id', $schoolIds));
        }

        return $query;
    }

    protected function mentionOptions(Get $get): array
    {
        $type = NotificationMentionType::tryFrom((string) $get('mention_type'));

        if (! $type) {
            return [];
        }

        $schoolIds = $this->clampSchools(array_values(array_map('intval', $get('school') ?? [])));
        $gradeIds = $this->clampGrades(array_values(array_map('intval', $get('grade') ?? [])));
        $classIds = array_values(array_map('intval', $get('class') ?? []));

        $model = match ($type) {
            NotificationMentionType::Lesson => Lesson::query()->with('learningClass.grade.school'),
            NotificationMentionType::Assignment => Assignment::query()->with('learningClass.grade.school'),
            NotificationMentionType::Quiz => Quiz::query()->with('learningClass.grade.school'),
        };

        if ($classIds) {
            $model->whereIn('learning_class_id', $classIds);
        } elseif ($gradeIds) {
            $model->whereHas('learningClass', fn ($q) => $q->whereIn('grade_id', $gradeIds));
        } elseif ($schoolIds) {
            $model->whereHas('learningClass.grade', fn ($q) => $q->whereIn('school_id', $schoolIds));
        }

        return $model->orderBy('title')->limit(500)->get()
            ->mapWithKeys(fn ($item) => [$item->getKey() => $item->title.' · '.($item->learningClass?->grade?->school?->name ?? '')])
            ->all();
    }

    public function content(Schema $schema): Schema
    {
        if (! $this->canSend()) {
            return $schema->components([]);
        }

        return $schema->components([
            $this->getComposeComponent(),
        ]);
    }

    protected function getComposeComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('send')
            ->footer([
                Actions::make([
                    Action::make('send')->label('Send Notification')->submit('send'),
                ]),
            ]);
    }

    public function send(): void
    {
        if (! $this->canSend()) {
            abort(403);
        }

        $data = $this->form->getState();
        $sender = auth()->user();
        $service = $this->getNotificationService();

        $data['recipient_type'] = NotificationRecipientType::tryFrom((string) ($data['recipient_type'] ?? ''));

        if (! $data['recipient_type'] instanceof NotificationRecipientType) {
            UiNotification::make()->title('Choose a recipient type.')->warning()->send();

            return;
        }

        if (filled($data['mention_type'] ?? null)) {
            $data['mention_type'] = NotificationMentionType::tryFrom((string) $data['mention_type']);
        } else {
            $data['mention_type'] = null;
        }

        // Normalize the shared School→Grade→Class cascade into the service contract.
        $data['schools'] = array_values(array_map('intval', $data['school'] ?? []));
        $data['grades'] = array_values(array_map('intval', $data['grade'] ?? []));
        $data['classes'] = array_values(array_map('intval', $data['class'] ?? []));

        $recipientIds = $service->resolveRecipients($sender, $data);

        if ($recipientIds->isEmpty()) {
            UiNotification::make()->title('No recipients matched.')->warning()->send();

            return;
        }

        $service->send($sender, $data, $recipientIds);

        UiNotification::make()
            ->title('Notification sent to '.$recipientIds->count().' recipient(s).')
            ->success()
            ->send();

        $this->form->fill();
    }

    /*
    |--------------------------------------------------------------------------
    | Listing
    |--------------------------------------------------------------------------
    */

    public function getViewData(): array
    {
        $user = auth()->user();
        $service = $this->getNotificationService();

        $notifications = $service->paginatedFor($user);

        return [
            'notifications' => $notifications,
            'canSend' => $this->canSend(),
            'readIds' => $service->readRecipientIdsFor($user),
            'deleteableIds' => collect($notifications->items())
                ->filter(fn (Notification $n) => $service->canDelete($user, $n))
                ->map(fn (Notification $n) => $n->getKey()),
        ];
    }

    public function deleteNotification(int $notificationId): void
    {
        $notification = Notification::find($notificationId);

        if (! $notification || ! $this->getNotificationService()->canDelete(auth()->user(), $notification)) {
            abort(403);
        }

        $notification->delete();

        UiNotification::make()->title('Notification deleted.')->success()->send();
    }

    public function markAsRead(int $notificationId): void
    {
        $notification = Notification::find($notificationId);

        if (! $notification) {
            return;
        }

        $this->getNotificationService()->markAsRead(auth()->user(), $notification);
    }

    public function mentionLabel(Notification $notification): ?string
    {
        return $this->getNotificationService()->mentionLabel($notification);
    }

    public function mentionUrl(Notification $notification): ?string
    {
        return $this->getNotificationService()->mentionUrl(auth()->user(), $notification);
    }
}

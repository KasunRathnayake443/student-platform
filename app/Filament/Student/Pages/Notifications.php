<?php

namespace App\Filament\Student\Pages;

use App\Models\Notification;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\StudentContextService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class Notifications extends Page
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.notifications';

    protected static ?string $slug = 'notifications';

    protected static ?string $title = 'Notifications';

    public ?Student $student = null;

    public string $tier = 'junior';

    /** @var array<string, mixed>|null */
    public ?array $activeContext = null;

    /** @var Collection<int, array<string, mixed>>|null */
    public ?Collection $allContexts = null;

    public string $notificationFilter = 'all';

    public string $notificationSearch = '';

    public function mount(): void
    {
        $user = Auth::user();
        /** @var Student|null $student */
        $student = $user?->student;
        $this->student = $student;

        if (! $this->student instanceof Student) {
            $this->redirect('/student');

            return;
        }

        $this->tier = $this->student->getAgeTier();

        $service = app(StudentContextService::class);
        $this->activeContext = $service->getActiveContext($this->student);
        $this->allContexts = $service->getContextsGroupedBySchool($this->student);

        $filter = request()->query('filter');
        if (is_string($filter) && in_array($filter, ['all', 'unread', 'assignment', 'quiz', 'lesson', 'announcement'], true)) {
            $this->notificationFilter = $filter;
        }
    }

    public function setNotificationFilter(string $filter): void
    {
        $this->notificationFilter = in_array($filter, ['all', 'unread', 'assignment', 'quiz', 'lesson', 'announcement'], true)
            ? $filter
            : 'all';
    }

    public function markNotificationAsRead(int $notificationId): void
    {
        $user = Auth::user();
        $notification = Notification::find($notificationId);

        if ($user && $notification) {
            app(NotificationService::class)->markAsRead($user, $notification);
            $this->dispatch('notification-updated');
        }
    }

    public function markAllNotificationsAsRead(): void
    {
        $user = Auth::user();

        if ($user) {
            app(NotificationService::class)->markAllAsRead($user);
            $this->dispatch('notification-updated');
        }
    }

    public function switchContext(string $key): void
    {
        if (! $this->student) {
            return;
        }

        $service = app(StudentContextService::class);
        $service->setActiveContext($this->student, $key);
        $this->activeContext = $service->getActiveContext($this->student);
        $this->allContexts = $service->getContextsGroupedBySchool($this->student);
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/student/login');
    }

    #[On('notification-updated')]
    public function onNotificationUpdated(): void
    {
        // Re-render
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $service = app(NotificationService::class);

        $query = Notification::query()->with(['sender', 'recipients']);
        $rawNotifications = $service->scopeQueryFor($user, $query)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $readIds = $service->readRecipientIdsFor($user);

        $allFormatted = $rawNotifications->map(function (Notification $n) use ($service, $user, $readIds) {
            $isRead = $readIds->contains($n->getKey());
            $mentionType = $n->mention_type?->value;

            return [
                'id' => $n->getKey(),
                'title' => $n->title,
                'body' => $n->body,
                'icon' => $n->icon,
                'color' => $n->color ?: 'primary',
                'mention_type' => $mentionType,
                'created_at' => $n->created_at,
                'sender' => $n->sender?->name ?? 'System',
                'is_read' => $isRead,
                'mention_label' => $service->mentionLabel($n),
                'mention_url' => $service->mentionUrl($user, $n),
            ];
        });

        $unreadCount = $allFormatted->where('is_read', false)->count();
        $assignmentCount = $allFormatted->where('mention_type', 'assignment')->count();
        $quizCount = $allFormatted->where('mention_type', 'quiz')->count();
        $lessonCount = $allFormatted->where('mention_type', 'lesson')->count();
        $announcementCount = $allFormatted->whereNull('mention_type')->count();

        $filtered = $allFormatted;

        if ($this->notificationFilter === 'unread') {
            $filtered = $filtered->where('is_read', false);
        } elseif ($this->notificationFilter === 'assignment') {
            $filtered = $filtered->where('mention_type', 'assignment');
        } elseif ($this->notificationFilter === 'quiz') {
            $filtered = $filtered->where('mention_type', 'quiz');
        } elseif ($this->notificationFilter === 'lesson') {
            $filtered = $filtered->where('mention_type', 'lesson');
        } elseif ($this->notificationFilter === 'announcement') {
            $filtered = $filtered->whereNull('mention_type');
        }

        if (filled($this->notificationSearch)) {
            $term = mb_strtolower(trim($this->notificationSearch));
            $filtered = $filtered->filter(function ($item) use ($term) {
                return str_contains(mb_strtolower($item['title'] ?? ''), $term)
                    || str_contains(mb_strtolower($item['body'] ?? ''), $term)
                    || str_contains(mb_strtolower($item['sender'] ?? ''), $term)
                    || str_contains(mb_strtolower($item['mention_label'] ?? ''), $term);
            });
        }

        return [
            'tier' => $this->tier,
            'student' => $this->student,
            'activeContext' => $this->activeContext,
            'allContexts' => $this->allContexts,
            'firstName' => explode(' ', $this->student?->user->name ?? 'Student')[0],
            'notifications' => $filtered->values(),
            'allNotificationsCount' => $allFormatted->count(),
            'notifStats' => [
                'total' => $allFormatted->count(),
                'unread' => $unreadCount,
                'assignments' => $assignmentCount,
                'quizzes' => $quizCount,
                'lessons' => $lessonCount,
                'announcements' => $announcementCount,
            ],
            'notificationFilter' => $this->notificationFilter,
            'notificationSearch' => $this->notificationSearch,
        ];
    }
}

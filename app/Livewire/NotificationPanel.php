<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationPanel extends Component
{
    public bool $open = false;

    public string $tier = 'junior';

    public string $filter = 'all'; // 'all' or 'unread'

    protected function getNotificationService(): NotificationService
    {
        return app(NotificationService::class);
    }

    public function getUnreadCount(): int
    {
        $user = auth()->user();

        return $user ? $this->getNotificationService()->unreadCount($user) : 0;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'unread'], true) ? $filter : 'all';
    }

    public function getRecentNotifications(): Collection
    {
        $service = $this->getNotificationService();
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        $readIds = $service->readRecipientIdsFor($user);

        $query = Notification::query()->with(['sender', 'recipients']);
        $notifications = $service->scopeQueryFor($user, $query)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $mapped = $notifications->map(function (Notification $n) use ($service, $user, $readIds) {
            $isRead = $readIds->contains($n->getKey());

            return [
                'id' => $n->getKey(),
                'title' => $n->title,
                'body' => $n->body,
                'icon' => $n->icon,
                'color' => $n->color ?: 'primary',
                'mention_type' => $n->mention_type?->value,
                'created_at' => $n->created_at,
                'sender' => $n->sender?->name ?? 'System',
                'is_read' => $isRead,
                'mention_label' => $service->mentionLabel($n),
                'mention_url' => $service->mentionUrl($user, $n),
            ];
        });

        if ($this->filter === 'unread') {
            $mapped = $mapped->filter(fn ($item) => ! $item['is_read'])->values();
        }

        return $mapped;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;

        // Opening the panel marks all notifications as read.
        if ($this->open) {
            $user = auth()->user();
            if ($user) {
                $this->getNotificationService()->markAllAsRead($user);
            }
        }
    }

    public function markAsRead(int $notificationId): void
    {
        $user = auth()->user();
        $notification = Notification::find($notificationId);

        if ($user && $notification) {
            $this->getNotificationService()->markAsRead($user, $notification);
            $this->dispatch('notification-updated');
        }
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();

        if ($user) {
            $this->getNotificationService()->markAllAsRead($user);
            $this->dispatch('notification-updated');
        }
    }

    public function viewAllNotifications(): void
    {
        $this->open = false;
        $this->dispatch('open-tab', tab: 'notifications');
    }

    #[On('notification-updated')]
    public function onNotificationUpdated(): void
    {
        // Re-render
    }

    public function render()
    {
        return view('livewire.notification-panel', [
            'unread' => $this->getUnreadCount(),
            'notifications' => $this->getRecentNotifications(),
            'open' => $this->open,
            'tier' => $this->tier,
            'filter' => $this->filter,
        ]);
    }
}


<?php

namespace App\Livewire;

use App\Filament\Pages\Notifications;
use App\Services\NotificationService;
use Filament\Facades\Filament;
use Livewire\Component;

class NotificationBell extends Component
{
    public function getUnreadCount(): int
    {
        return app(NotificationService::class)->unreadCount(auth()->user());
    }

    public function getNotificationsUrl(): string
    {
        $panel = Filament::getCurrentPanel();

        $pageClass = match ($panel?->getId()) {
            'admin' => Notifications::class,
            'school-admin' => \App\Filament\SchoolAdmin\Pages\Notifications::class,
            'teacher' => \App\Filament\Teacher\Pages\Notifications::class,
            'student' => \App\Filament\Student\Pages\Notifications::class,
            default => null,
        };

        if ($pageClass) {
            return $pageClass::getUrl(panel: $panel?->getId());
        }

        return '#';
    }

    public function render()
    {
        return view('livewire.notification-bell', [
            'unread' => $this->getUnreadCount(),
            'notificationsUrl' => $this->getNotificationsUrl(),
        ]);
    }
}

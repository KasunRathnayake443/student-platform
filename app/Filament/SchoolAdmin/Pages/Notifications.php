<?php

namespace App\Filament\SchoolAdmin\Pages;

use App\Enums\NotificationRecipientType;
use App\Filament\Notifications\NotificationsCenter;

class Notifications extends NotificationsCenter
{
    protected string $view = 'filament.pages.notifications';

    protected static ?string $title = 'Notifications';

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }

    protected function recipientTypeOptions(): array
    {
        return [
            NotificationRecipientType::Student->value => 'Students',
            NotificationRecipientType::Teacher->value => 'Teachers',
        ];
    }

    protected function schoolOptions(): array
    {
        return auth()->user()->schools()->orderBy('schools.name')->pluck('schools.name', 'schools.id')->all();
    }
}

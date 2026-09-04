<?php

namespace App\Filament\Pages;

use App\Enums\NotificationRecipientType;
use App\Filament\Notifications\NotificationsCenter;
use App\Models\School;

class Notifications extends NotificationsCenter
{
    protected string $view = 'filament.pages.notifications';

    protected static ?string $title = 'Notifications';

    protected function recipientTypeOptions(): array
    {
        return NotificationRecipientType::options();
    }

    protected function schoolOptions(): array
    {
        return School::orderBy('name')->pluck('name', 'id')->all();
    }

    protected function clampSchools(array $requested): array
    {
        return $requested;
    }
}

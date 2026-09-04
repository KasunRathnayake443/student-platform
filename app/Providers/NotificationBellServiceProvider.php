<?php

namespace App\Providers;

use App\Livewire\NotificationBell;
use App\Livewire\NotificationPanel;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class NotificationBellServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('notification-bell', NotificationBell::class);
        Livewire::component('notification-panel', NotificationPanel::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_END,
            fn (): string => $this->renderBellForDefaultPanels(),
        );
    }

    protected function renderBellForDefaultPanels(): string
    {
        $panelId = Filament::getCurrentPanel()?->getId();

        if (! in_array($panelId, ['admin'], true)) {
            return '';
        }

        if (! auth()->check()) {
            return '';
        }

        return Blade::render('<livewire:notification-bell />');
    }
}

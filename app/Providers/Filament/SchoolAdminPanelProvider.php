<?php

namespace App\Providers\Filament;

use App\Filament\SchoolAdmin\Pages\Auth\Login;
use App\Filament\SchoolAdmin\Pages\SchoolAdminDashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SchoolAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('school-admin')
            ->path('school-admin')
            ->brandName('Student Platform')
            ->login(Login::class)
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::Fuchsia,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->darkMode(false)
            ->discoverResources(in: app_path('Filament/SchoolAdmin/Resources'), for: 'App\Filament\SchoolAdmin\Resources')
            ->discoverPages(in: app_path('Filament/SchoolAdmin/Pages'), for: 'App\Filament\SchoolAdmin\Pages')
            ->pages([
                SchoolAdminDashboard::class,
            ])
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

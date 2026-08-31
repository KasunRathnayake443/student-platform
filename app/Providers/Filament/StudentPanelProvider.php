<?php

namespace App\Providers\Filament;

use App\Filament\Student\Pages\Auth\StudentLogin;
use App\Filament\Student\Pages\AssignmentView;
use App\Filament\Student\Pages\Dashboard;
use App\Filament\Student\Pages\LessonView;
use App\Filament\Student\Pages\QuizAttempt;
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

class StudentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('student')
            ->path('student')
            ->brandName('Student Platform')
            ->login(StudentLogin::class)
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::Violet,
            ])
            ->pages([
                Dashboard::class,
                LessonView::class,
                AssignmentView::class,
                QuizAttempt::class,
            ])
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

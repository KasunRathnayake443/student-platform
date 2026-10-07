<?php

namespace App\Providers;

use App\Services\PlatformSettings;
use Carbon\CarbonImmutable;
use Filament\Auth\Notifications\ResetPassword as FilamentResetPasswordNotification;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB; // <-- 1. Add this import at the top
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Filament queues product reset emails (ShouldQueue), but no queue worker
        // runs in the local environment (QUEUE_CONNECTION=database). Dispatch them
        // over the "sync" connection so they are sent immediately within the request,
        // exactly like the test email from the platform settings page.
        $this->app->bind(FilamentResetPasswordNotification::class, function (Container $app, array $parameters): FilamentResetPasswordNotification {
            $notification = new FilamentResetPasswordNotification($parameters['token']);
            $notification->onConnection('sync');

            return $notification;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191); // <-- 2. Add this line here

        $this->configureDefaults();

        $this->applyPlatformSettings();
    }

    /**
     * Load branding and password-reset email settings stored by super admins.
     *
     * The queue worker and every request re-boot the application, so this is
     * applied once at boot from the single platform_settings row. Restart any
     * long-running `queue:work` process after changing these settings.
     */
    protected function applyPlatformSettings(): void
    {
        try {
            if (Schema::hasTable('platform_settings')) {
                PlatformSettings::apply();
            }
        } catch (Throwable) {
            // The database may be unavailable during early boot (migrations,
            // config caching, etc.). The default .env configuration stays active.
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

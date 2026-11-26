<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Contracts\Encryption\DecryptException;

final class PlatformSettings
{
    private static ?PlatformSetting $cached = null;

    public static function current(): ?PlatformSetting
    {
        return self::$cached ??= PlatformSetting::query()->first();
    }

    public static function flushCache(): void
    {
        self::$cached = null;
    }

    public static function apply(): void
    {
        $settings = self::current();

        if (! $settings instanceof PlatformSetting) {
            return;
        }

        if (filled($settings->platform_name)) {
            config()->set('app.name', $settings->platform_name);
        }

        $overrides = [];

        try {
            $mailPassword = $settings->mail_password;
        } catch (DecryptException) {
            // The stored password was encrypted with a different APP_KEY (for
            // example after an environment change or database import), so it
            // can no longer be decrypted. Ignore it instead of failing every
            // request that applies the platform settings.
            $mailPassword = null;
        }

        foreach ([
            'host' => $settings->mail_host,
            'port' => $settings->mail_port,
            'username' => $settings->mail_username,
            'password' => $mailPassword,
        ] as $key => $value) {
            if (filled($value)) {
                $overrides[$key] = $value;
            }
        }

        if (filled($settings->mail_encryption)) {
            $overrides['scheme'] = match ($settings->mail_encryption) {
                'ssl' => 'smtps',
                'tls' => 'smtp',
                default => null,
            };
            $overrides['auto_tls'] = $settings->mail_encryption === 'tls';
            $overrides['require_tls'] = $settings->mail_encryption === 'tls';
        }

        if ($overrides !== []) {
            config()->set('mail.mailers.smtp', array_merge(
                (array) config('mail.mailers.smtp', []),
                $overrides,
            ));
        }

        if (filled($settings->mail_from_address)) {
            config()->set('mail.from.address', $settings->mail_from_address);
        }

        if (filled($settings->mail_from_name)) {
            config()->set('mail.from.name', $settings->mail_from_name);
        }
    }

    public static function hasConfiguredWriter(): bool
    {
        $settings = self::current();

        return $settings instanceof PlatformSetting
            && filled($settings->mail_host)
            && filled($settings->mail_username)
            && filled($settings->mail_from_address);
    }

    public static function name(): string
    {
        $settings = self::current();

        if ($settings instanceof PlatformSetting && filled($settings->platform_name)) {
            return (string) $settings->platform_name;
        }

        return (string) config('app.name');
    }

    public static function logoUrl(): string
    {
        $settings = self::current();

        if ($settings instanceof PlatformSetting && filled($settings->platform_logo)) {
            return (string) $settings->logo_url;
        }

        $path = public_path('images/platform-logo.svg');
        $version = is_file($path) ? filemtime($path) : null;

        return (string) asset('images/platform-logo.svg'.($version ? '?v='.$version : ''));
    }

    public static function contactAddress(): ?string
    {
        return self::contactValue('contact_address');
    }

    public static function contactEmail(): ?string
    {
        return self::contactValue('contact_email');
    }

    public static function contactPhone(): ?string
    {
        return self::contactValue('contact_phone');
    }

    /**
     * @return array<string, string>
     */
    public static function socialLinks(): array
    {
        $settings = self::current();

        if (! $settings instanceof PlatformSetting) {
            return [];
        }

        $links = array_filter([
            'facebook' => $settings->facebook_url,
            'youtube' => $settings->youtube_url,
            'tiktok' => $settings->tiktok_url,
            'instagram' => $settings->instagram_url,
        ], fn ($value): bool => filled($value));

        return array_map('strval', $links);
    }

    private static function contactValue(string $key): ?string
    {
        $settings = self::current();

        if (! $settings instanceof PlatformSetting || blank($settings->{$key})) {
            return null;
        }

        return (string) $settings->{$key};
    }
}

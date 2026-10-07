<?php

namespace App\Services;

use App\Models\PlatformSetting;

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

        foreach ([
            'host' => $settings->mail_host,
            'port' => $settings->mail_port,
            'username' => $settings->mail_username,
            'password' => $settings->mail_password,
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

    public static function logoUrl(): string
    {
        $settings = self::current();

        if ($settings instanceof PlatformSetting && filled($settings->platform_logo)) {
            return (string) $settings->logo_url;
        }

        return (string) asset('images/platform-logo.svg');
    }
}

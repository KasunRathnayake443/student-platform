<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PlatformSetting extends Model
{
    public const SINGLETON_ID = 1;

    protected $fillable = [
        'platform_name',
        'platform_tagline',
        'platform_logo',
        'mail_from_name',
        'mail_from_address',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
    ];

    protected $casts = [
        'mail_password' => 'encrypted',
        'mail_port' => 'integer',
    ];

    protected $hidden = [
        'mail_password',
    ];

    public static function settings(): static
    {
        return static::query()->firstOrCreate(
            ['id' => self::SINGLETON_ID],
        );
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->platform_logo)) {
            return null;
        }

        if ((string) config('filament.default_filesystem_disk', 'local') === 'public') {
            return Storage::disk('public')->url($this->platform_logo);
        }

        return route('platform.logo');
    }
}

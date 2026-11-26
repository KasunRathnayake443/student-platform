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
        'contact_address',
        'contact_email',
        'contact_phone',
        'facebook_url',
        'youtube_url',
        'tiktok_url',
        'instagram_url',
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

        $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));
        $version = $this->updated_at?->timestamp;

        if (! $version && $disk->exists($this->platform_logo)) {
            $version = $disk->lastModified($this->platform_logo);
        }

        if ((string) config('filament.default_filesystem_disk', 'local') === 'public') {
            $url = $disk->url($this->platform_logo);

            return $version ? $url.(str_contains($url, '?') ? '&' : '?').'v='.$version : $url;
        }

        return route('platform.logo', $version ? ['v' => $version] : []);
    }
}

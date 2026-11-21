<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlatformLogoController extends Controller
{
    public function __invoke(): BinaryFileResponse|StreamedResponse
    {
        $settings = PlatformSetting::settings();
        $logo = $settings->platform_logo;

        $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));

        abort_if(blank($logo), 404);
        abort_if(! $disk->exists($logo), 404);

        $response = $disk->response($logo);
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 24 * 30);
        $response->headers->addCacheControlDirective('immutable');

        return $response;
    }
}

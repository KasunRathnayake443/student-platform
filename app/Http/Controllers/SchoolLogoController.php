<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolLogoController extends Controller
{
    public function __invoke(School $school): StreamedResponse
    {
        $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));

        abort_if(blank($school->logo), 404);
        abort_if(! $disk->exists($school->logo), 404);

        return $disk->response($school->logo);
    }
}

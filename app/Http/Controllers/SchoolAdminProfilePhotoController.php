<?php

namespace App\Http\Controllers;

use App\Models\SchoolAdmin;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolAdminProfilePhotoController extends Controller
{
    public function __invoke(SchoolAdmin $schoolAdmin): StreamedResponse
    {
        $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));

        abort_if(blank($schoolAdmin->profile_photo), 404);
        abort_if(! $disk->exists($schoolAdmin->profile_photo), 404);

        return $disk->response($schoolAdmin->profile_photo);
    }
}

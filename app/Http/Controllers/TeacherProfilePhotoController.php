<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherProfilePhotoController extends Controller
{
    public function __invoke(Teacher $teacher): StreamedResponse
    {
        $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));

        abort_if(blank($teacher->profile_photo), 404);
        abort_if(! $disk->exists($teacher->profile_photo), 404);

        return $disk->response($teacher->profile_photo);
    }
}

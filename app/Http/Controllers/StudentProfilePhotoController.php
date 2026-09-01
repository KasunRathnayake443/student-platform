<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentProfilePhotoController extends Controller
{
    public function __invoke(Student $student): StreamedResponse
    {
        $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));

        abort_if(blank($student->profile_photo), 404);
        abort_if(! $disk->exists($student->profile_photo), 404);

        return $disk->response($student->profile_photo);
    }
}

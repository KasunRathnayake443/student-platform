<?php

namespace App\Http\Controllers;

use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizQuestionMediaController extends Controller
{
    public function __invoke(QuizQuestion $quizQuestion, string $type): BinaryFileResponse|StreamedResponse
    {
        $diskName = (string) config('filament.default_filesystem_disk', 'local');
        $disk = Storage::disk($diskName);

        $path = match ($type) {
            'image' => $quizQuestion->question_image,
            'video' => $quizQuestion->question_video,
            default => null,
        };

        abort_if(blank($path), 404);
        abort_if(! $disk->exists($path), 404);

        // BinaryFileResponse handles HTTP Range requests, which browsers need
        // for reliable video playback and seeking.
        if ($diskName === 'local') {
            return response()->file($disk->path($path));
        }

        return $disk->response($path);
    }
}

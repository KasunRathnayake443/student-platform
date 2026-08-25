<?php

namespace App\Http\Controllers;

use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizQuestionMediaController extends Controller
{
    public function __invoke(QuizQuestion $quizQuestion, string $type): StreamedResponse
    {
        $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));

        $path = match ($type) {
            'image' => $quizQuestion->question_image,
            'video' => $quizQuestion->question_video,
            default => null,
        };

        abort_if(blank($path), 404);
        abort_if(! $disk->exists($path), 404);

        return $disk->response($path);
    }
}

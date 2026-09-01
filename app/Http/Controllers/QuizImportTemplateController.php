<?php

namespace App\Http\Controllers;

use App\Services\QuizImportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizImportTemplateController extends Controller
{
    public function __invoke(QuizImportService $service): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print ($service->buildTemplate()),
            'quiz-questions-template.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}

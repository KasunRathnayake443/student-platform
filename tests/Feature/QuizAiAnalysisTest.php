<?php

use App\Filament\Student\Pages\QuizAttempt as QuizAttemptPage;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Database\Seeders\PlatformSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(PlatformSeeder::class);

    config()->set('services.gemini.key', 'test-gemini-key');
    config()->set('services.gemini.model', 'gemini-test-model');
});

/**
 * Create a quiz with one correct and one incorrect answer, then submit it.
 *
 * @return array{0: Quiz, 1: Student, 2: QuizAttempt}
 */
function makeAiAnalysisQuizAndAttempt(): array
{
    $sourceQuiz = Quiz::query()->where('is_published', true)->firstOrFail();

    $student = Student::query()
        ->whereHas('enrollments', function ($query) use ($sourceQuiz) {
            $query->where('status', 'active')->whereHas('classes', function ($classQuery) use ($sourceQuiz) {
                $classQuery->where('learning_classes.id', $sourceQuiz->learning_class_id);
            });
        })
        ->firstOrFail();

    $quiz = Quiz::create([
        'learning_class_id' => $sourceQuiz->learning_class_id,
        'teacher_id' => $sourceQuiz->teacher_id,
        'title' => 'Fractions and Geometry Check',
        'description' => 'A short quiz used to test AI learning insights.',
        'time_limit_minutes' => null,
        'max_attempts' => 1,
        'passing_percentage' => 50,
        'total_points' => 2,
        'availability_type' => 'immediate',
        'is_published' => true,
        'feedback_mode' => Quiz::FEEDBACK_END_OF_QUIZ,
    ]);

    $fractions = $quiz->questions()->create([
        'question_text' => 'What is 1/2 + 1/4?',
        'points' => 1,
        'sort_order' => 1,
    ]);
    $fractionsCorrect = $fractions->options()->create([
        'option_text' => '3/4',
        'is_correct' => true,
        'sort_order' => 1,
    ]);
    $fractions->options()->create([
        'option_text' => '1/2',
        'is_correct' => false,
        'sort_order' => 2,
    ]);

    $geometry = $quiz->questions()->create([
        'question_text' => 'How many sides does a triangle have?',
        'points' => 1,
        'sort_order' => 2,
    ]);
    $geometry->options()->create([
        'option_text' => '3',
        'is_correct' => true,
        'sort_order' => 1,
    ]);
    $geometryWrong = $geometry->options()->create([
        'option_text' => '4',
        'is_correct' => false,
        'sort_order' => 2,
    ]);

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'started_at' => now()->subMinutes(10),
        'expires_at' => null,
        'status' => 'in_progress',
    ]);

    $attempt->recordAnswer($fractions->id, $fractionsCorrect->id);
    $attempt->recordAnswer($geometry->id, $geometryWrong->id);
    $attempt->submit();

    return [$quiz, $student, $attempt->fresh()];
}

function geminiAnalysisResponseBody(): array
{
    return [
        'candidates' => [[
            'content' => [
                'parts' => [[
                    'text' => json_encode([
                        'summary' => 'Great effort! You are strong with fractions and should practise geometry vocabulary.',
                        'strengths' => [
                            [
                                'area' => 'Fractions',
                                'score' => 100,
                                'comment' => 'You added fractions accurately.',
                            ],
                        ],
                        'focus_areas' => [
                            [
                                'area' => 'Geometry',
                                'score' => 0,
                                'comment' => 'Review the properties of basic shapes.',
                                'suggestion' => 'Draw and label a triangle, square, and pentagon.',
                            ],
                        ],
                        'next_steps' => [
                            'Practise one geometry question each day.',
                        ],
                    ]),
                ]],
            ],
        ]],
    ];
}

function fakeGeminiAnalysisResponse(): void
{
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(geminiAnalysisResponseBody(), 200),
    ]);
}

test('quiz results generate and store AI learning insights', function () {
    [$quiz, $student, $attempt] = makeAiAnalysisQuizAndAttempt();

    fakeGeminiAnalysisResponse();

    Livewire::actingAs($student->user)
        ->withQueryParams(['quiz' => $quiz->id])
        ->test(QuizAttemptPage::class)
        ->call('generateQuizAnalysis')
        ->assertSet('aiAnalysisLoading', false)
        ->assertSet('aiAnalysisError', null)
        ->assertSee('Your Learning Insights')
        ->assertSee('Strongest areas')
        ->assertSee('Fractions')
        ->assertSee('Areas to focus on')
        ->assertSee('Geometry')
        ->assertSee('Practise one geometry question each day.');

    $attempt->refresh();

    expect($attempt->ai_analysis_status)->toBe('completed')
        ->and($attempt->ai_analysis_generated_at)->not->toBeNull()
        ->and($attempt->ai_analysis_error)->toBeNull()
        ->and($attempt->ai_analysis)->toBeArray()
        ->and($attempt->ai_analysis['strengths'][0]['area'])->toBe('Fractions')
        ->and($attempt->ai_analysis['focus_areas'][0]['area'])->toBe('Geometry');

    Http::assertSent(function (Request $request) {
        $payload = json_decode($request->body(), true);
        $prompt = data_get($payload, 'contents.0.parts.0.text', '');
        $systemPrompt = data_get($payload, 'systemInstruction.parts.0.text', '');

        return $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && str_contains((string) $request->url(), 'gemini-test-model:generateContent')
            && str_contains($prompt, 'What is 1/2 + 1/4?')
            && str_contains($prompt, '3/4')
            && str_contains($prompt, 'How many sides does a triangle have?')
            && str_contains($prompt, '"student_answer": "4"')
            && str_contains($systemPrompt, 'Never repeat or reveal')
            && str_contains($systemPrompt, 'same language');
    });
});

test('quiz results stay available when AI analysis fails and the analysis can be retried', function () {
    [$quiz, $student, $attempt] = makeAiAnalysisQuizAndAttempt();

    Http::fakeSequence('generativelanguage.googleapis.com/*')
        ->push(['error' => ['message' => 'Quota exceeded.']], 429)
        ->push(geminiAnalysisResponseBody(), 200);

    $component = Livewire::actingAs($student->user)
        ->withQueryParams(['quiz' => $quiz->id])
        ->test(QuizAttemptPage::class)
        ->call('generateQuizAnalysis')
        ->assertSet('aiAnalysisLoading', false)
        ->assertSee('We could not generate your learning insights right now. Please try again.')
        ->assertSee('Try again');

    $attempt->refresh();

    expect($attempt->ai_analysis_status)->toBe('failed')
        ->and($attempt->ai_analysis)->toBeNull()
        ->and($attempt->ai_analysis_error)->not->toBeNull();

    $component
        ->call('generateQuizAnalysis')
        ->assertSee('Your Learning Insights')
        ->assertSee('Fractions');

    expect($attempt->fresh()->ai_analysis_status)->toBe('completed');
});

test('score-only quizzes do not show or generate AI learning insights', function () {
    [$quiz, $student, $attempt] = makeAiAnalysisQuizAndAttempt();

    $quiz->update(['feedback_mode' => Quiz::FEEDBACK_SCORE_ONLY]);

    Http::fake();

    Livewire::actingAs($student->user)
        ->withQueryParams(['quiz' => $quiz->id])
        ->test(QuizAttemptPage::class)
        ->call('generateQuizAnalysis')
        ->assertDontSee('Your Learning Insights')
        ->assertDontSee('Analysing your answers');

    Http::assertNothingSent();

    $attempt->refresh();

    expect($attempt->ai_analysis_status)->toBeNull()
        ->and($attempt->ai_analysis)->toBeNull();
});

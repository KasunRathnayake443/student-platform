<?php

use App\Filament\Resources\Quizzes\Pages\CreateQuiz as AdminCreateQuiz;
use App\Filament\SchoolAdmin\Resources\Quizzes\Pages\CreateQuiz as SchoolAdminCreateQuiz;
use App\Filament\Teacher\Resources\Quizzes\Pages\CreateQuiz as TeacherCreateQuiz;
use App\Models\LearningClass;
use App\Models\Quiz;
use App\Models\User;
use App\Services\QuizQuestionGeneratorService;
use Database\Seeders\PlatformSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PlatformSeeder::class);

    config()->set('services.gemini.key', 'test-gemini-key');
    config()->set('services.gemini.model', 'gemini-test-model');
});

/**
 * @param  array<int, array<string, mixed>>  $questions
 */
function fakeGeminiQuizQuestionsResponse(array $questions): void
{
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'text' => json_encode(['questions' => $questions]),
                    ]],
                ],
            ]],
        ], 200),
    ]);
}

/**
 * @return array<int, array<string, mixed>>
 */
function fakeQuizQuestionsPayload(): array
{
    return [
        [
            'question_text' => 'Which gas do plants absorb during photosynthesis?',
            'explanation' => 'Plants take in carbon dioxide and release oxygen.',
            'options' => [
                ['option_text' => 'Oxygen', 'is_correct' => false],
                ['option_text' => 'Carbon dioxide', 'is_correct' => true],
                ['option_text' => 'Nitrogen', 'is_correct' => false],
            ],
        ],
        [
            'question_text' => 'What is the process called?',
            'explanation' => 'The process is photosynthesis.',
            'options' => [
                ['option_text' => 'Respiration', 'is_correct' => false],
                ['option_text' => 'Photosynthesis', 'is_correct' => false],
            ],
        ],
        [
            'question_text' => 'This extra question should be trimmed.',
            'explanation' => 'Only the requested number of questions is kept.',
            'options' => [
                ['option_text' => 'A', 'is_correct' => true],
                ['option_text' => 'B', 'is_correct' => true],
            ],
        ],
    ];
}

test('the AI question generator normalises questions into the repeater shape', function () {
    fakeGeminiQuizQuestionsResponse(fakeQuizQuestionsPayload());

    $questions = app(QuizQuestionGeneratorService::class)->generate([
        'prompt' => 'Photosynthesis',
        'question_count' => 2,
        'grade_level' => 'Grade 10',
        'language' => 'English',
        'difficulty' => 'Medium',
        'points_per_question' => 2,
    ]);

    expect($questions)->toHaveCount(2)
        ->and($questions[0]['question_text'])->toBe('Which gas do plants absorb during photosynthesis?')
        ->and($questions[0]['points'])->toBe(2)
        ->and($questions[0]['question_image'])->toBeNull()
        ->and($questions[0]['question_video'])->toBeNull()
        ->and($questions[0]['options'])->toHaveCount(3)
        ->and(collect($questions[0]['options'])->where('is_correct', true))->toHaveCount(1)
        ->and(collect($questions[0]['options'])->firstWhere('is_correct', true)['option_text'])->toBe('Carbon dioxide')
        ->and(collect($questions[1]['options'])->where('is_correct', true))->toHaveCount(1)
        ->and(collect($questions[1]['options'])->firstWhere('is_correct', true)['option_text'])->toBe('Respiration');

    Http::assertSent(function (Request $request) {
        $payload = json_decode($request->body(), true);
        $prompt = data_get($payload, 'contents.0.parts.0.text', '');
        $systemPrompt = data_get($payload, 'systemInstruction.parts.0.text', '');

        return $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && str_contains((string) $request->url(), 'gemini-test-model:generateContent')
            && str_contains($prompt, 'Photosynthesis')
            && str_contains($prompt, 'Grade 10')
            && str_contains($prompt, '"requested_question_count": 2')
            && str_contains($prompt, '"points_per_question": 2')
            && str_contains($systemPrompt, 'exactly one unambiguously correct option')
            && str_contains($systemPrompt, 'untrusted user data');
    });
});

test('the AI question generator keeps the correct answer when AI returns too many options', function () {
    $options = [];

    for ($i = 1; $i <= 6; $i++) {
        $options[] = ['option_text' => "Distractor {$i}", 'is_correct' => false];
    }

    $options[] = ['option_text' => 'The correct answer', 'is_correct' => true];
    $options[] = ['option_text' => 'Another distractor', 'is_correct' => false];

    fakeGeminiQuizQuestionsResponse([
        [
            'question_text' => 'Which answer is correct?',
            'explanation' => 'The seventh option is correct.',
            'options' => $options,
        ],
    ]);

    $questions = app(QuizQuestionGeneratorService::class)->generate([
        'prompt' => 'Options test',
        'question_count' => 1,
        'grade_level' => 'Grade 10',
    ]);

    expect($questions[0]['options'])->toHaveCount(6)
        ->and(collect($questions[0]['options'])->where('is_correct', true))->toHaveCount(1)
        ->and(collect($questions[0]['options'])->firstWhere('is_correct', true)['option_text'])->toBe('The correct answer');
});

test('the AI question generator requires a configured Gemini key', function () {
    config()->set('services.gemini.key', '');
    Http::fake();

    expect(fn () => app(QuizQuestionGeneratorService::class)->generate([
        'prompt' => 'Photosynthesis',
        'question_count' => 2,
        'grade_level' => 'Grade 10',
    ]))->toThrow(RuntimeException::class, 'The Gemini API key is not configured.');

    Http::assertNothingSent();
});

test('the quiz create page adds AI-generated questions to the question builder', function () {
    fakeGeminiQuizQuestionsResponse(fakeQuizQuestionsPayload());

    $admin = User::where('email', 'admin1@example.com')->firstOrFail();
    Filament::setCurrentPanel('admin');
    $this->actingAs($admin);

    $class = LearningClass::where('name', '10-B Science & Physics')->firstOrFail();

    Livewire::test(AdminCreateQuiz::class)
        ->fillForm([
            'title' => 'AI Generated Quiz',
            'learning_class_id' => $class->getKey(),
            'teacher_ids' => [$class->teachers()->firstOrFail()->getKey()],
            'passing_percentage' => 50,
            'availability_type' => 'immediate',
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'questions' => [
                [
                    'question_text' => 'Existing question',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'A', 'is_correct' => true],
                        ['option_text' => 'B', 'is_correct' => false],
                    ],
                ],
            ],
        ])
        ->callAction(TestAction::make('generateQuestionsWithAi')->schemaComponent(), [
            'prompt' => 'Photosynthesis',
            'question_count' => 1,
            'grade_level' => 'Grade 10',
            'language' => 'English',
            'difficulty' => 'Medium',
            'points_per_question' => 2,
        ])
        ->assertHasNoFormErrors()
        ->assertSet('data.questions.0.question_text', 'Existing question')
        ->assertSet('data.questions.1.question_text', 'Which gas do plants absorb during photosynthesis?')
        ->assertSet('data.questions.1.points', 2)
        ->assertSet('data.questions.1.options.1.option_text', 'Carbon dioxide')
        ->assertSet('data.questions.1.options.1.is_correct', true)
        ->call('create')
        ->assertHasNoFormErrors();

    $quiz = Quiz::where('title', 'AI Generated Quiz')->firstOrFail();
    $generatedQuestion = $quiz->questions()
        ->where('question_text', 'Which gas do plants absorb during photosynthesis?')
        ->first();

    expect($quiz->questions()->count())->toBe(2)
        ->and($generatedQuestion)->not->toBeNull()
        ->and($generatedQuestion->points)->toBe(2)
        ->and($generatedQuestion->options()->count())->toBe(3)
        ->and($generatedQuestion->options()->where('is_correct', true)->count())->toBe(1);
});

test('the AI generator pre-fills the grade and language from the selected class', function () {
    $admin = User::where('email', 'admin1@example.com')->firstOrFail();
    Filament::setCurrentPanel('admin');
    $this->actingAs($admin);

    $class = LearningClass::where('name', '10-B Science & Physics')->firstOrFail();
    $language = in_array($class->medium, ['English', 'Sinhala', 'Tamil'], true)
        ? $class->medium
        : 'English';

    Livewire::test(AdminCreateQuiz::class)
        ->fillForm(['learning_class_id' => $class->getKey()])
        ->mountAction(TestAction::make('generateQuestionsWithAi')->schemaComponent())
        ->assertSchemaStateSet([
            'grade_level' => $class->grade->name,
            'language' => $language,
            'question_count' => 5,
        ]);
});

test('a failed AI request leaves the question builder unchanged', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'error' => ['message' => 'Quota exceeded.'],
        ], 429),
    ]);

    $admin = User::where('email', 'admin1@example.com')->firstOrFail();
    Filament::setCurrentPanel('admin');
    $this->actingAs($admin);

    $class = LearningClass::where('name', '10-B Science & Physics')->firstOrFail();

    Livewire::test(AdminCreateQuiz::class)
        ->fillForm([
            'title' => 'AI Failure Quiz',
            'learning_class_id' => $class->getKey(),
            'teacher_ids' => [$class->teachers()->firstOrFail()->getKey()],
            'passing_percentage' => 50,
            'availability_type' => 'immediate',
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'questions' => [
                [
                    'question_text' => 'Existing question',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'A', 'is_correct' => true],
                        ['option_text' => 'B', 'is_correct' => false],
                    ],
                ],
            ],
        ])
        ->callAction(TestAction::make('generateQuestionsWithAi')->schemaComponent(), [
            'prompt' => 'Photosynthesis',
            'question_count' => 2,
            'grade_level' => 'Grade 10',
            'language' => 'English',
            'difficulty' => 'Medium',
            'points_per_question' => 1,
        ])
        ->assertSet('data.questions.0.question_text', 'Existing question')
        ->assertSet('data.questions.1', null);
});

test('the AI question generator appears on quiz create pages in every dashboard', function () {
    $cases = [
        ['panel' => 'admin', 'email' => 'admin1@example.com', 'page' => AdminCreateQuiz::class],
        ['panel' => 'school-admin', 'email' => 'schooladmin1@example.com', 'page' => SchoolAdminCreateQuiz::class],
        ['panel' => 'teacher', 'email' => 'teacher1@example.com', 'page' => TeacherCreateQuiz::class],
    ];

    foreach ($cases as $case) {
        Filament::setCurrentPanel($case['panel']);

        Livewire::actingAs(User::where('email', $case['email'])->firstOrFail())
            ->test($case['page'])
            ->assertSee('Generate Questions with AI')
            ->assertActionExists(TestAction::make('generateQuestionsWithAi')->schemaComponent());
    }
});

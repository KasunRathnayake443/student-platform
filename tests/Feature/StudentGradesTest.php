<?php

use App\Filament\Student\Pages\Dashboard;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Database\Seeders\PlatformSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PlatformSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('student'));
});

/**
 * Create a quiz in a class the student belongs to, with a correct and an
 * incorrect option for each question.
 *
 * @return array<string, mixed>
 */
function makeGradesQuizScenario(): array
{
    $sourceQuiz = Quiz::query()->where('is_published', true)->firstOrFail();

    $student = Student::query()
        ->whereHas('enrollments', function ($query) use ($sourceQuiz) {
            $query->where('status', 'active')->whereHas('classes', function ($classQuery) use ($sourceQuiz) {
                $classQuery->where('learning_classes.id', $sourceQuiz->learning_class_id);
            });
        })
        ->firstOrFail();

    // Keep the grades tab predictable for this student.
    $student->quizAttempts()->delete();

    $quiz = Quiz::create([
        'learning_class_id' => $sourceQuiz->learning_class_id,
        'teacher_id' => $sourceQuiz->teacher_id,
        'title' => 'Fractions and Geometry Check',
        'description' => 'A short quiz used to test the grades tab.',
        'time_limit_minutes' => null,
        'max_attempts' => 3,
        'passing_percentage' => 50,
        'total_points' => 2,
        'availability_type' => 'immediate',
        'is_published' => true,
        'feedback_mode' => Quiz::FEEDBACK_END_OF_QUIZ,
    ]);

    $fractions = $quiz->questions()->create([
        'question_text' => 'What is 1/2 + 1/4?',
        'explanation' => 'Find a common denominator before adding the fractions.',
        'points' => 1,
        'sort_order' => 1,
    ]);
    $fractionsCorrect = $fractions->options()->create([
        'option_text' => '3/4',
        'is_correct' => true,
        'sort_order' => 1,
    ]);
    $fractionsWrong = $fractions->options()->create([
        'option_text' => '1/2',
        'is_correct' => false,
        'sort_order' => 2,
    ]);

    $geometry = $quiz->questions()->create([
        'question_text' => 'How many sides does a triangle have?',
        'explanation' => 'A triangle always has three sides.',
        'points' => 1,
        'sort_order' => 2,
    ]);
    $geometryCorrect = $geometry->options()->create([
        'option_text' => '3',
        'is_correct' => true,
        'sort_order' => 1,
    ]);
    $geometryWrong = $geometry->options()->create([
        'option_text' => '4',
        'is_correct' => false,
        'sort_order' => 2,
    ]);

    return [
        'quiz' => $quiz,
        'student' => $student,
        'fractions' => $fractions,
        'fractions_correct' => $fractionsCorrect,
        'fractions_wrong' => $fractionsWrong,
        'geometry' => $geometry,
        'geometry_correct' => $geometryCorrect,
        'geometry_wrong' => $geometryWrong,
    ];
}

/**
 * Submit an attempt with one correct and one incorrect answer.
 *
 * @param  array<string, mixed>  $scenario
 */
function submitGradesAttempt(array $scenario, int $attemptNumber, bool $fractionsCorrect = true, bool $geometryCorrect = false): QuizAttempt
{
    $attempt = QuizAttempt::create([
        'quiz_id' => $scenario['quiz']->id,
        'student_id' => $scenario['student']->id,
        'attempt_number' => $attemptNumber,
        'started_at' => now()->subMinutes(10),
        'expires_at' => null,
        'status' => 'in_progress',
    ]);

    $attempt->recordAnswer(
        $scenario['fractions']->id,
        $fractionsCorrect ? $scenario['fractions_correct']->id : $scenario['fractions_wrong']->id,
    );
    $attempt->recordAnswer(
        $scenario['geometry']->id,
        $geometryCorrect ? $scenario['geometry_correct']->id : $scenario['geometry_wrong']->id,
    );

    $attempt->submit();

    return $attempt->fresh();
}

test('student grades tab groups attempts and opens answers with AI insight', function () {
    $scenario = makeGradesQuizScenario();
    $firstAttempt = submitGradesAttempt($scenario, 1, true, false);
    $secondAttempt = submitGradesAttempt($scenario, 2, false, true);

    $secondAttempt->update([
        'ai_analysis' => [
            'summary' => 'You know shape facts well and should keep practising fractions.',
            'strengths' => [
                [
                    'area' => 'Geometry',
                    'score' => 100,
                    'comment' => 'You identified the number of sides correctly.',
                ],
            ],
            'focus_areas' => [
                [
                    'area' => 'Fractions',
                    'score' => 0,
                    'comment' => 'Review how to find a common denominator.',
                    'suggestion' => 'Practise adding fractions with different denominators.',
                ],
            ],
            'next_steps' => [
                'Complete five fraction addition questions this week.',
            ],
        ],
        'ai_analysis_status' => 'completed',
        'ai_analysis_generated_at' => now(),
    ]);

    $component = Livewire::actingAs($scenario['student']->user)
        ->test(Dashboard::class)
        ->call('setTab', 'grades')
        ->assertSuccessful()
        ->assertSee('Fractions and Geometry Check')
        ->assertSee('2 attempt(s)')
        ->assertSee('Attempt #1')
        ->assertSee('Attempt #2')
        ->assertSee('grade-quiz-toggle', false);

    // Both the kids card layout and the older-student table layout use the
    // same grouped data and attempt expansion.
    $component
        ->set('tier', 'kids')
        ->assertSee('kids-grade-card', false)
        ->assertSee('View attempts')
        ->set('tier', 'senior')
        ->assertSee('grade-attempts-row', false);

    $component
        ->call('openQuizAttemptDetail', $secondAttempt->id)
        ->assertSet('gradeDetailOpen', true)
        ->assertSee('AI Learning Insights')
        ->assertSee('Geometry')
        ->assertSee('Fractions')
        ->assertSee('What is 1/2 + 1/4?')
        ->assertSee('Find a common denominator before adding the fractions.')
        ->assertSee('How many sides does a triangle have?')
        ->assertSee('3/4')
        ->assertSee('1/2')
        ->assertSee('Correct answer')
        ->assertSee('Your choice')
        ->assertSee('Practise adding fractions with different denominators.');

    expect($component->get('gradeDetail')['id'])->toBe($secondAttempt->id)
        ->and($component->get('gradeDetail')['correct_count'])->toBe(1)
        ->and($component->get('gradeDetail')['total_questions'])->toBe(2)
        ->and($component->get('gradeDetail')['ai_analysis']['strengths'][0]['area'])->toBe('Geometry');
});

test('score-only quizzes expose no answer or AI details in the grades tab', function () {
    $scenario = makeGradesQuizScenario();
    $scenario['quiz']->update(['feedback_mode' => Quiz::FEEDBACK_SCORE_ONLY]);

    $attempt = submitGradesAttempt($scenario, 1);

    $component = Livewire::actingAs($scenario['student']->user)
        ->test(Dashboard::class)
        ->call('setTab', 'grades')
        ->assertSuccessful()
        ->assertSee('Fractions and Geometry Check')
        ->assertSee('Answers hidden')
        ->assertDontSee('See answers');

    $component
        ->call('openQuizAttemptDetail', $attempt->id)
        ->assertSet('gradeDetailOpen', false)
        ->assertSet('gradeDetail', null);

    expect($component->get('gradeDetail'))->toBeNull();
});

test('students cannot open another students attempt from the grades tab', function () {
    $scenario = makeGradesQuizScenario();

    $otherStudent = Student::query()
        ->whereKeyNot($scenario['student']->id)
        ->whereHas('enrollments', function ($query) use ($scenario) {
            $query->where('status', 'active')->whereHas('classes', function ($classQuery) use ($scenario) {
                $classQuery->where('learning_classes.id', $scenario['quiz']->learning_class_id);
            });
        })
        ->firstOrFail();

    $otherAttempt = QuizAttempt::create([
        'quiz_id' => $scenario['quiz']->id,
        'student_id' => $otherStudent->id,
        'attempt_number' => 1,
        'started_at' => now()->subMinutes(10),
        'expires_at' => null,
        'status' => 'in_progress',
    ]);
    $otherAttempt->recordAnswer($scenario['fractions']->id, $scenario['fractions_correct']->id);
    $otherAttempt->submit();

    $component = Livewire::actingAs($scenario['student']->user)
        ->test(Dashboard::class)
        ->call('setTab', 'grades');

    $component
        ->call('openQuizAttemptDetail', $otherAttempt->id)
        ->assertSet('gradeDetailOpen', false)
        ->assertSet('gradeDetail', null);

    expect($component->get('gradeDetail'))->toBeNull();
});

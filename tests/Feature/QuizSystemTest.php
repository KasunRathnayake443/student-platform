<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Database\Seeders\PlatformSeeder;

beforeEach(function () {
    $this->seed(PlatformSeeder::class);
});

test('quizzes and questions are seeded with correct relationships', function () {
    $quiz = Quiz::with(['questions.options', 'teacher', 'learningClass'])->first();

    expect($quiz)->not->toBeNull()
        ->and($quiz->questions)->not->toBeEmpty()
        ->and($quiz->total_points)->toBeGreaterThan(0);

    $question = $quiz->questions->first();
    expect($question->options)->not->toBeEmpty()
        ->and($question->options->where('is_correct', true)->count())->toBe(1);
});

test('quiz attempt records answers incrementally and calculates remaining time', function () {
    $quiz = Quiz::where('time_limit_minutes', 30)->first();
    $student = Student::first();

    // Start a new attempt
    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_id' => $student->id,
        'attempt_number' => 2,
        'started_at' => now(),
        'expires_at' => now()->addMinutes(30),
        'status' => 'in_progress',
    ]);

    expect($attempt->isTimeExpired())->toBeFalse()
        ->and($attempt->getRemainingSeconds())->toBeGreaterThan(1700)
        ->and($attempt->getRemainingSeconds())->toBeLessThanOrEqual(1800);

    // Save answer to question 1
    $question1 = $quiz->questions->first();
    $correctOption = $question1->options->firstWhere('is_correct', true);

    $answer = $attempt->recordAnswer($question1->id, $correctOption->id);

    expect($answer)->not->toBeNull()
        ->and($answer->is_correct)->toBeTrue()
        ->and((float) $answer->points_awarded)->toBe((float) $question1->points);
});

test('quiz attempt grades properly on submission', function () {
    $quiz = Quiz::with('questions.options')->first();
    $student = Student::first();

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_id' => $student->id,
        'attempt_number' => 3,
        'started_at' => now()->subMinutes(15),
        'expires_at' => now()->addMinutes(15),
        'status' => 'in_progress',
    ]);

    // Answer all questions correctly
    foreach ($quiz->questions as $q) {
        $correctOpt = $q->options->firstWhere('is_correct', true);
        $attempt->recordAnswer($q->id, $correctOpt->id);
    }

    $attempt->submit();

    expect($attempt->status)->toBe('submitted')
        ->and($attempt->completed_at)->not->toBeNull()
        ->and((float) $attempt->percentage)->toBe(100.00)
        ->and($attempt->is_passed)->toBeTrue();
});

test('expired quiz attempt auto-submits and prevents further answer modifications', function () {
    $quiz = Quiz::with('questions.options')->first();
    $student = Student::first();

    // Expired attempt
    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_id' => $student->id,
        'attempt_number' => 4,
        'started_at' => now()->subMinutes(40),
        'expires_at' => now()->subMinutes(10), // Expired 10 minutes ago
        'status' => 'in_progress',
    ]);

    expect($attempt->isTimeExpired())->toBeTrue()
        ->and($attempt->getRemainingSeconds())->toBe(0);

    // Attempting to record answer should trigger auto-submit and return null
    $question1 = $quiz->questions->first();
    $correctOption = $question1->options->firstWhere('is_correct', true);

    $result = $attempt->recordAnswer($question1->id, $correctOption->id);

    expect($result)->toBeNull()
        ->and($attempt->fresh()->status)->toBe('time_expired');
});

test('quiz attempt component saves answer without advancing to next question', function () {
    $quiz = Quiz::with('questions.options', 'learningClass.grade')->where('is_published', true)->first();
    
    $student = Student::whereHas('enrollments', function ($q) use ($quiz) {
        $q->where('status', 'active')->whereHas('classes', function ($cq) use ($quiz) {
            $cq->where('learning_classes.id', $quiz->learning_class_id);
        });
    })->first();

    if (! $student) {
        $student = Student::first();
        $enrollment = $student->enrollments()->create([
            'school_id' => $quiz->learningClass->grade->school_id,
            'grade_id' => $quiz->learningClass->grade_id,
            'status' => 'active',
            'academic_year' => '2026',
            'enrolled_at' => now(),
        ]);
        $enrollment->classes()->attach($quiz->learning_class_id);
    }

    $user = $student->user;

    \Livewire\Livewire::actingAs($user)
        ->withQueryParams(['quiz' => $quiz->id])
        ->test(\App\Filament\Student\Pages\QuizAttempt::class)
        ->call('startAttempt')
        ->assertSet('currentIndex', 0)
        ->tap(function ($component) use ($quiz) {
            $firstQuestion = $quiz->questions->first();
            $option = $firstQuestion->options->first();

            $component->call('selectAnswer', $firstQuestion->id, $option->id);
            // Verify currentIndex did NOT change
            $component->assertSet('currentIndex', 0);
            $component->assertSet("answers.{$firstQuestion->id}", $option->id);

            // Also test pickAnswer does not auto-advance
            $component->call('pickAnswer', $firstQuestion->id, $option->id);
            $component->assertSet('currentIndex', 0);

            // Next button navigates to question 2
            $component->call('nextOrSubmit');
            $component->assertSet('currentIndex', 1);

            // Prev button goes back to question 1
            $component->call('previousQuestion');
            $component->assertSet('currentIndex', 0);
        });
});


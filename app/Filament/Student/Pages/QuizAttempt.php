<?php

namespace App\Filament\Student\Pages;

use App\Models\Quiz;
use App\Models\QuizAttempt as QuizAttemptModel;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\Student;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;

class QuizAttempt extends Page
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.quiz-attempt';

    protected static ?string $slug = 'quiz-attempt';

    protected static bool $shouldRegisterNavigation = false;

    public ?Quiz $quiz = null;

    public string $tier = 'junior';

    public ?string $schoolName = null;

    public ?string $gradeName = null;

    public ?string $className = null;

    public ?int $questionCount = null;

    public ?int $timeLimit = null;

    public ?int $passingPercentage = null;

    public ?int $maxAttempts = null;

    public ?int $attemptId = null;

    public ?int $attemptNumber = null;

    public int $currentIndex = 0;

    /** @var array<int, int|null> question id => chosen option id */
    public array $answers = [];

    public ?int $remainingSeconds = null;

    public bool $finished = false;

    public bool $wasTimeExpired = false;

    public ?float $finalPercentage = null;

    public ?float $finalScore = null;

    public bool $passed = false;

    public ?int $answeredCount = null;

    public string $notice = '';

    public string $coverState = 'closed';

    public bool $showCorrect = false;

    public bool $canTryAgain = false;

    #[Locked]
    public ?QuizAttemptModel $attempt = null;

    #[Locked]
    public ?Student $student = null;

    public function mount(): void
    {
        $user = Auth::user();
        $student = $user?->student;

        if (! $student) {
            $this->redirect('/student');

            return;
        }

        $this->student = $student;

        $quizId = (int) request()->query('quiz');

        /** @var Quiz|null $quiz */
        $quiz = Quiz::with([
            'learningClass.grade.school',
            'learningClass.teachers.user',
            'teacher.user',
        ])
            ->where('is_published', true)
            ->find($quizId);

        if (! $quiz) {
            abort(404);
        }

        // The student must be enrolled in the quiz's class before attempting it.
        $enrollments = $student->enrollments()
            ->with(['school', 'grade', 'classes'])
            ->where('status', 'active')
            ->get();

        $enrolledInClass = $enrollments->contains(
            fn ($enrollment) => $enrollment->classes->contains('id', $quiz->learning_class_id)
        );

        if (! $enrolledInClass) {
            abort(403);
        }

        $class = $quiz->learningClass;

        $this->quiz = $quiz;
        $this->tier = $student->getAgeTier();
        $this->schoolName = $class?->grade?->school?->name ?? 'My School';
        $this->gradeName = $class?->grade?->name ?? null;
        $this->className = $class?->name ?? 'General Class';
        $this->questionCount = $quiz->questions()->count();
        $this->timeLimit = $quiz->time_limit_minutes;
        $this->passingPercentage = $quiz->passing_percentage;
        $this->maxAttempts = $quiz->max_attempts;
        $this->showCorrect = (bool) $quiz->show_correct_answers_after_submission;

        // Resume an in-progress attempt (or finalise it if time already ran out).
        $inProgress = $student->quizAttempts()
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        if ($inProgress) {
            if ($inProgress->autoSubmitIfExpired()) {
                $this->loadFinished($inProgress->refresh());
            } else {
                $this->loadActive($inProgress);
            }

            return;
        }

        $this->setupCoverState();
    }

    /*
    |--------------------------------------------------------------------------
    | State loading
    |--------------------------------------------------------------------------
    */

    protected function setupCoverState(): void
    {
        $quiz = $this->quiz;
        $student = $this->student;

        $attempts = $student->quizAttempts()
            ->where('quiz_id', $quiz->id)
            ->orderByDesc('completed_at')
            ->get();

        $finishedCount = $attempts->whereIn('status', ['submitted', 'time_expired'])->count();
        $canAttempt = $quiz->canStudentAttempt($student);

        if (! $quiz->isAvailable()) {
            $this->coverState = 'locked';
        } elseif ($quiz->isExpired()) {
            $this->coverState = 'closed';
        } elseif (! $canAttempt) {
            $this->coverState = 'complete';

            if ($latestFinished = $attempts->first()) {
                $this->loadFinished($latestFinished);
                $this->notice = '';
            }
        } else {
            $this->coverState = 'available';
        }

        $this->maxAttempts = $quiz->max_attempts;
        $this->questionCount = $quiz->questions()->count();
    }

    protected function loadActive(QuizAttemptModel $attempt): void
    {
        $attempt->load('answers');

        $this->attempt = $attempt;
        $this->attemptId = $attempt->id;
        $this->attemptNumber = $attempt->attempt_number;
        $this->remainingSeconds = $attempt->getRemainingSeconds();
        $this->finished = false;
        $this->currentIndex = 0;

        $this->answers = $attempt->answers->mapWithKeys(
            fn ($answer) => [$answer->quiz_question_id => $answer->quiz_question_option_id]
        )->all();

        if ($attempt->isTimeExpired()) {
            $this->notice = 'Your quiz time has run out, so your answers were submitted automatically.';
            $this->wasTimeExpired = true;
        }
    }

    protected function loadFinished(QuizAttemptModel $attempt): void
    {
        $attempt->load('answers');

        $this->attempt = $attempt;
        $this->attemptId = $attempt->id;
        $this->attemptNumber = $attempt->attempt_number;
        $this->finished = true;
        $this->remainingSeconds = 0;
        $this->wasTimeExpired = $attempt->status === 'time_expired';
        $this->finalPercentage = $attempt->percentage !== null ? (float) $attempt->percentage : 0.0;
        $this->finalScore = $attempt->score !== null ? (float) $attempt->score : 0.0;
        $this->passed = (bool) $attempt->is_passed;
        $this->answeredCount = $attempt->answers()
            ->whereNotNull('quiz_question_option_id')
            ->count();
        $this->answers = $attempt->answers->mapWithKeys(
            fn ($answer) => [$answer->quiz_question_id => $answer->quiz_question_option_id]
        )->all();

        $this->canTryAgain = $this->quiz->canStudentAttempt($this->student);
    }

    /*
    |--------------------------------------------------------------------------
    | Question & option ordering (stable per attempt, honours shuffle flags)
    |--------------------------------------------------------------------------
    */

    public function orderedQuestions(): Collection
    {
        $questions = $this->quiz->questions()->with('options')->get();

        if ($this->quiz->shuffle_questions) {
            $seed = 1000 + (int) ($this->attemptId ?? 0);

            return $questions
                ->sortBy(fn (QuizQuestion $q) => $this->stableHash($seed + (int) $q->id))
                ->values();
        }

        return $questions->values();
    }

    /**
     * @return Collection<int, QuizQuestionOption>
     */
    public function orderedOptions(QuizQuestion $question): Collection
    {
        $options = $question->options;
        if ($options === null) {
            return collect();
        }

        if ($this->quiz->shuffle_options) {
            $seed = 1_000_000 + (int) ($this->attemptId ?? 0) * 100 + (int) $question->id;

            return $options
                ->sortBy(fn (QuizQuestionOption $o) => $this->stableHash($seed + (int) $o->id))
                ->values();
        }

        return $options->values();
    }

    protected function stableHash(int $value): int
    {
        $value = (($value % 1000003) + 1000003) % 1000003; // keep positive
        $value = (($value * 9301) + 49297) % 233280;

        return $value;
    }

    public function setProgress(): void
    {
        // placeholder to keep Livewire diffs simple; state lives in currentIndex
    }

    /*
    |--------------------------------------------------------------------------
    | Taking the quiz
    |--------------------------------------------------------------------------
    */

    public function startAttempt(): void
    {
        $quiz = $this->quiz;
        $student = $this->student;

        if (! $student || ! $this->quiz) {
            return;
        }

        if (! $quiz->isAvailable() || $quiz->isExpired()) {
            $this->notice = 'This quiz is not available to take right now.';

            return;
        }

        if (! $quiz->canStudentAttempt($student)) {
            $this->notice = 'You have used up your attempts for this quiz.';

            return;
        }

        $attemptNumber = $quiz->attempts()
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'time_expired'])
            ->count() + 1;

        $startedAt = now();
        $expiresAt = $quiz->time_limit_minutes
            ? $startedAt->copy()->addMinutes($quiz->time_limit_minutes)
            : null;

        /** @var QuizAttemptModel $attempt */
        $attempt = QuizAttemptModel::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'attempt_number' => $attemptNumber,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'status' => 'in_progress',
        ]);

        $this->loadActive($attempt->load('answers'));
    }

    public function selectAnswer(int $questionId, int $optionId): void
    {
        if ($this->finished || ! $this->attempt) {
            return;
        }

        if ($this->attempt->isTimeExpired() || $this->attempt->isFinished()) {
            $this->attempt->autoSubmitIfExpired();
            $this->loadFinished($this->attempt->refresh());

            return;
        }

        $option = QuizQuestionOption::find($optionId);
        if ($option && (int) $option->quiz_question_id === $questionId) {
            $this->attempt->recordAnswer($questionId, $optionId);
        }

        $this->answers[$questionId] = $optionId;
    }

    public function pickAnswer(int $questionId, int $optionId): void
    {
        $this->selectAnswer($questionId, $optionId);

        if ($this->finished || ! $this->attempt) {
            return;
        }

        if (! $this->isLastQuestion()) {
            $this->currentIndex++;
        }
    }

    public function goToQuestion(int $index): void
    {
        $total = $this->questionCount ?: 0;
        $this->currentIndex = max(0, min($total - 1, $index));
    }

    public function previousQuestion(): void
    {
        $this->goToQuestion($this->currentIndex - 1);
    }

    public function isLastQuestion(): bool
    {
        return $this->currentIndex >= ($this->questionCount ?: 1) - 1;
    }

    public function nextOrSubmit(): void
    {
        if ($this->finished || ! $this->attempt) {
            return;
        }

        if (! $this->isLastQuestion()) {
            $this->currentIndex++;

            return;
        }

        $this->submitAttempt();
    }

    /**
     * Called by the on-page countdown when the timer reaches zero.
     */
    public function timeUp(): void
    {
        if ($this->finished || ! $this->attempt) {
            return;
        }

        $this->attempt->submit('time_expired');
        $this->loadFinished($this->attempt->refresh());
    }

    public function submitAttempt(): void
    {
        if ($this->finished || ! $this->attempt) {
            $this->notice = 'No active attempt to submit.';

            return;
        }

        if ($this->attempt->isTimeExpired()) {
            $this->attempt->submit('time_expired');
        } else {
            $this->attempt->submit('submitted');
        }

        $this->loadFinished($this->attempt->refresh());
    }

    public function getTitle(): string
    {
        return $this->quiz?->title ?? 'Quiz';
    }
}

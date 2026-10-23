<?php

namespace App\Livewire\Kids;

use App\Models\KidsGameScore;
use App\Models\Student;
use App\Services\KidsGameService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The kids (ages 5-10) Game Arcade.
 *
 * The hub, the age filters and the results screen are server rendered. Each
 * individual game runs in the browser (it needs animation frames) and reports
 * back the number of correct answers and the number of rounds; the score and
 * the star rating are always calculated here.
 */
class Games extends Component
{
    /** Key of the game being played — empty string means the hub is showing. */
    public string $activeGame = '';

    /** Active age filter chip. */
    public string $filter = KidsGameService::AGE_ALL;

    /** True once the current game has reported a result. */
    public bool $justFinished = false;

    public int $lastCorrect = 0;

    public int $lastTotal = 0;

    public int $lastScore = 0;

    public int $lastStars = 0;

    /**
     * Switch the age filter chips.
     */
    public function setFilter(string $filter): void
    {
        if (! array_key_exists($filter, app(KidsGameService::class)->ageFilters())) {
            return;
        }

        $this->filter = $filter;
    }

    /**
     * Open one of the catalog games.
     */
    public function startGame(string $key): void
    {
        if (! array_key_exists($key, app(KidsGameService::class)->catalog())) {
            return;
        }

        $this->activeGame = $key;
        $this->resetResult();
    }

    /**
     * Restart the game that is currently open.
     */
    public function playAgain(): void
    {
        $this->startGame($this->activeGame);
    }

    /**
     * Leave the arcade and go back to the games hub.
     */
    public function backToHub(): void
    {
        $this->activeGame = '';
        $this->resetResult();
    }

    /**
     * Store the result reported by a game.
     *
     * The browser only reports how many rounds were answered correctly and how
     * many rounds there were — the points and the stars are worked out here.
     */
    public function finishGame(int $correct, int $total): void
    {
        if ($this->activeGame === '') {
            return;
        }

        $total = max(1, min(60, $total));
        $correct = max(0, min($correct, $total));

        $this->lastTotal = $total;
        $this->lastCorrect = $correct;
        $this->lastScore = $correct * 10;
        $this->lastStars = app(KidsGameService::class)->starsFor($correct, $total);
        $this->justFinished = true;

        $this->recordResult();

        $this->dispatch('kids-game-complete', stars: $this->lastStars);
    }

    /**
     * Best score, stars and play count for every game in the catalog.
     *
     * @return array<string, array{best_score: int, stars: int, plays: int}>
     */
    public function gameStats(): array
    {
        $scores = $this->student()?->gameScores()->get()->keyBy('game_key') ?? collect();

        $stats = [];

        foreach (app(KidsGameService::class)->catalog() as $key => $game) {
            $row = $scores->get($key);

            $stats[$key] = [
                'best_score' => (int) ($row->best_score ?? 0),
                'stars' => (int) ($row->stars ?? 0),
                'plays' => (int) ($row->plays ?? 0),
            ];
        }

        return $stats;
    }

    public function render(): View
    {
        $service = app(KidsGameService::class);
        $student = $this->student();

        $catalog = $service->catalog();
        $stats = $this->gameStats();

        $groups = [];

        foreach ($service->subjects() as $subjectKey => $subject) {
            $games = [];

            foreach ($catalog as $game) {
                if ($game['subject'] !== $subjectKey) {
                    continue;
                }

                if (! $service->matchesFilter($game, $this->filter)) {
                    continue;
                }

                $games[] = $game;
            }

            if ($games === []) {
                continue;
            }

            $groups[] = [
                'subject' => $subject,
                'games' => $games,
            ];
        }

        return view('livewire.kids.games', [
            'catalog' => $catalog,
            'stats' => $stats,
            'groups' => $groups,
            'ageFilters' => $service->ageFilters(),
            'ageBand' => $student ? $service->ageBand($student) : KidsGameService::AGE_EARLY,
            'totalStars' => array_sum(array_column($stats, 'stars')),
            'totalPlays' => array_sum(array_column($stats, 'plays')),
            'maxStars' => KidsGameService::MAX_STARS,
            'activeGameMeta' => $catalog[$this->activeGame] ?? null,
        ]);
    }

    /**
     * Persist the best result for the game that just finished.
     */
    protected function recordResult(): void
    {
        $student = $this->student();

        if (! $student || $this->activeGame === '') {
            return;
        }

        $record = KidsGameScore::firstOrNew([
            'student_id' => $student->id,
            'game_key' => $this->activeGame,
        ]);

        $record->best_score = max((int) $record->best_score, $this->lastScore);
        $record->stars = max((int) $record->stars, $this->lastStars);
        $record->plays = ((int) $record->plays) + 1;
        $record->last_played_at = now();
        $record->save();
    }

    protected function resetResult(): void
    {
        $this->justFinished = false;
        $this->lastCorrect = 0;
        $this->lastTotal = 0;
        $this->lastScore = 0;
        $this->lastStars = 0;
    }

    protected function student(): ?Student
    {
        $user = auth()->user();
        $student = $user?->student;

        return $student instanceof Student ? $student : null;
    }
}

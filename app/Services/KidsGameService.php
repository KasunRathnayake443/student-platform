<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Carbon;

/**
 * Catalog and scoring rules for the kids (ages 5-10) Game Arcade.
 *
 * Every game is age/general based: the content is built from the child's age
 * band rather than their classwork, so a game is always playable.
 */
class KidsGameService
{
    /* ---------------------------------------------------------------------
    | Subjects
    | ------------------------------------------------------------------ */

    public const SUBJECT_MATH = 'math';

    public const SUBJECT_WORDS = 'words';

    public const SUBJECT_PUZZLES = 'puzzles';

    public const SUBJECT_ART = 'art';

    /* ---------------------------------------------------------------------
    | Age bands (the kids dashboard covers ages 5-10)
    | ------------------------------------------------------------------ */

    /** Shown to every child on the dashboard. */
    public const AGE_ALL = 'all';

    /** Ages 5-7 — "Early Learners". */
    public const AGE_EARLY = 'early';

    /** Ages 8-10 — "Explorers". */
    public const AGE_EXPLORER = 'explorer';

    public const MAX_STARS = 3;

    /** @var array<string, array{key: string, label: string, emoji: string}> */
    private const SUBJECTS = [
        self::SUBJECT_MATH => ['key' => self::SUBJECT_MATH, 'label' => 'Math & Numbers', 'emoji' => '🔢'],
        self::SUBJECT_WORDS => ['key' => self::SUBJECT_WORDS, 'label' => 'Language & Literacy', 'emoji' => '🔤'],
        self::SUBJECT_PUZZLES => ['key' => self::SUBJECT_PUZZLES, 'label' => 'Logic & Puzzles', 'emoji' => '🧩'],
        self::SUBJECT_ART => ['key' => self::SUBJECT_ART, 'label' => 'Creativity & Art', 'emoji' => '🎨'],
    ];

    /** @var array<string, array{key: string, label: string, hint: string, emoji: string}> */
    private const AGE_FILTERS = [
        self::AGE_ALL => ['key' => self::AGE_ALL, 'label' => 'All Ages', 'hint' => '5-10', 'emoji' => '🎮'],
        self::AGE_EARLY => ['key' => self::AGE_EARLY, 'label' => 'Early Learners', 'hint' => '5-7', 'emoji' => '🐣'],
        self::AGE_EXPLORER => ['key' => self::AGE_EXPLORER, 'label' => 'Explorers', 'hint' => '8-10', 'emoji' => '🚀'],
    ];

    /**
     * The four subject areas shown on the games hub.
     *
     * @return array<string, array{key: string, label: string, emoji: string}>
     */
    public function subjects(): array
    {
        return self::SUBJECTS;
    }

    /**
     * The age filter chips shown above the game grid.
     *
     * @return array<string, array{key: string, label: string, hint: string, emoji: string}>
     */
    public function ageFilters(): array
    {
        return self::AGE_FILTERS;
    }

    /**
     * Every game in the arcade, keyed by its route/action name.
     *
     * @return array<string, array{key: string, title: string, emoji: string, tagline: string, description: string, subject: string, ages: array<int, string>, skill: string, theme: string}>
     */
    public function catalog(): array
    {
        return [
            // ── Math & Numbers ────────────────────────────────────────────
            'number-catch' => [
                'key' => 'number-catch',
                'title' => 'Number Catch',
                'emoji' => '🧺',
                'tagline' => 'Catch the right numbers!',
                'description' => 'Slide the basket under the falling numbers and catch the ones the monster asks for.',
                'subject' => self::SUBJECT_MATH,
                'ages' => [self::AGE_EARLY],
                'skill' => 'Counting',
                'theme' => 'sky',
            ],
            'math-runner' => [
                'key' => 'math-runner',
                'title' => 'Math Runner',
                'emoji' => '🏃',
                'tagline' => 'Solve it and dash through!',
                'description' => 'Pick the right answer to open the gate and keep your runner going.',
                'subject' => self::SUBJECT_MATH,
                'ages' => [self::AGE_EXPLORER],
                'skill' => 'Times tables',
                'theme' => 'grape',
            ],

            // ── Language & Literacy ───────────────────────────────────────
            'phonics-balloon' => [
                'key' => 'phonics-balloon',
                'title' => 'Phonics Balloon Pop',
                'emoji' => '🎈',
                'tagline' => 'Pop the right sound!',
                'description' => 'Listen to the sound and pop the balloon that has the matching letter.',
                'subject' => self::SUBJECT_WORDS,
                'ages' => [self::AGE_EARLY],
                'skill' => 'Phonics',
                'theme' => 'mint',
            ],
            'word-builder' => [
                'key' => 'word-builder',
                'title' => 'Word Builder',
                'emoji' => '🔤',
                'tagline' => 'Build the word, tile by tile!',
                'description' => 'Tap the letter tiles in the right order to spell the word.',
                'subject' => self::SUBJECT_WORDS,
                'ages' => [self::AGE_EXPLORER],
                'skill' => 'Spelling',
                'theme' => 'sunny',
            ],

            // ── Logic & Problem Solving ───────────────────────────────────
            'memory-match' => [
                'key' => 'memory-match',
                'title' => 'Memory Match',
                'emoji' => '🃏',
                'tagline' => 'Find all the matching pairs!',
                'description' => 'Flip the cards over and match every pair before you run out of moves.',
                'subject' => self::SUBJECT_PUZZLES,
                'ages' => [self::AGE_ALL],
                'skill' => 'Memory',
                'theme' => 'coral',
            ],
            'coding-blocks' => [
                'key' => 'coding-blocks',
                'title' => 'Coding Blocks',
                'emoji' => '🧩',
                'tagline' => 'Program a path to the star!',
                'description' => 'Place arrow blocks, run your code and guide the robot to the goal.',
                'subject' => self::SUBJECT_PUZZLES,
                'ages' => [self::AGE_ALL],
                'skill' => 'Logic',
                'theme' => 'sky',
            ],

            // ── Creativity & Fine Motor ───────────────────────────────────
            'pixel-painter' => [
                'key' => 'pixel-painter',
                'title' => 'Pixel Art Painter',
                'emoji' => '🎨',
                'tagline' => 'Paint the picture by numbers!',
                'description' => 'Pick a colour, then fill in every tile to reveal the hidden picture.',
                'subject' => self::SUBJECT_ART,
                'ages' => [self::AGE_ALL],
                'skill' => 'Creativity',
                'theme' => 'mint',
            ],
        ];
    }

    /**
     * The age band a student falls into on the kids dashboard.
     */
    public function ageBand(Student $student): string
    {
        $age = $student->date_of_birth
            ? Carbon::parse($student->date_of_birth)->age
            : 8;

        return $age <= 7 ? self::AGE_EARLY : self::AGE_EXPLORER;
    }

    /**
     * A game belongs to a filter when it is listed under it, or when it is
     * flagged as suitable for every age band.
     *
     * @param  array{key: string, title: string, emoji: string, tagline: string, description: string, subject: string, ages: array<int, string>, skill: string, theme: string}  $game
     */
    public function matchesFilter(array $game, string $filter): bool
    {
        if ($filter === self::AGE_ALL) {
            return true;
        }

        return in_array($filter, $game['ages'], true)
            || in_array(self::AGE_ALL, $game['ages'], true);
    }

    /**
     * Turn a raw score into a star rating (0 - 3).
     */
    public function starsFor(int $correct, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        $ratio = $correct / $total;

        return match (true) {
            $ratio >= 0.9 => 3,
            $ratio >= 0.6 => 2,
            $ratio >= 0.3 => 1,
            default => 0,
        };
    }
}

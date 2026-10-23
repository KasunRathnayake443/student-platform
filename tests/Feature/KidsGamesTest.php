<?php

use App\Filament\Student\Pages\Dashboard;
use App\Livewire\Kids\Games;
use App\Models\KidsGameScore;
use App\Models\User;
use App\Services\KidsGameService;
use Database\Seeders\PlatformSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PlatformSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('student'));

    // Leo Mason — age 9, so he lands on the kids dashboard.
    $this->kidsUser = User::where('email', 'student9@example.com')->firstOrFail();
    $this->kidsStudent = $this->kidsUser->student;

    // Liam Miller — a senior student.
    $this->seniorUser = User::where('email', 'student1@example.com')->firstOrFail();
    $this->seniorStudent = $this->seniorUser->student;
});

/*
|--------------------------------------------------------------------------
| The catalog
|--------------------------------------------------------------------------
*/

test('the arcade catalog holds the seven kids games', function () {
    $catalog = app(KidsGameService::class)->catalog();

    expect($catalog)->toHaveCount(7)
        ->and(array_keys($catalog))->toBe([
            'number-catch',
            'math-runner',
            'phonics-balloon',
            'word-builder',
            'memory-match',
            'coding-blocks',
            'pixel-painter',
        ]);
});

test('every catalog game is complete and grouped under a subject', function () {
    $service = app(KidsGameService::class);
    $subjects = array_keys($service->subjects());

    foreach ($service->catalog() as $game) {
        expect($game['title'])->not->toBeEmpty()
            ->and($game['emoji'])->not->toBeEmpty()
            ->and($game['tagline'])->not->toBeEmpty()
            ->and($game['description'])->not->toBeEmpty()
            ->and($game['skill'])->not->toBeEmpty()
            ->and($subjects)->toContain($game['subject'])
            ->and($game['ages'])->not->toBeEmpty();
    }
});

test('stars are awarded according to the percentage of correct answers', function () {
    $service = app(KidsGameService::class);

    expect($service->starsFor(10, 10))->toBe(3)
        ->and($service->starsFor(9, 10))->toBe(3)
        ->and($service->starsFor(6, 10))->toBe(2)
        ->and($service->starsFor(3, 10))->toBe(1)
        ->and($service->starsFor(2, 10))->toBe(0)
        ->and($service->starsFor(0, 0))->toBe(0);
});

test('the age band splits the kids dashboard at seven years old', function () {
    $service = app(KidsGameService::class);

    // Leo Mason is nine, so he is an Explorer.
    expect($service->ageBand($this->kidsStudent))->toBe(KidsGameService::AGE_EXPLORER);

    $younger = $this->kidsStudent->replicate();
    $younger->exists = false;
    $younger->date_of_birth = now()->subYears(6);

    expect($service->ageBand($younger))->toBe(KidsGameService::AGE_EARLY);
});

/*
|--------------------------------------------------------------------------
| Hub and filters
|--------------------------------------------------------------------------
*/

test('the games hub lists every game grouped by subject', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Games::class)
        ->assertSee('Game Arcade')
        ->assertSee('Math & Numbers')
        ->assertSee('Number Catch')
        ->assertSee('Math Runner')
        ->assertSee('Language & Literacy')
        ->assertSee('Phonics Balloon Pop')
        ->assertSee('Word Builder')
        ->assertSee('Logic & Puzzles')
        ->assertSee('Memory Match')
        ->assertSee('Coding Blocks')
        ->assertSee('Creativity & Art')
        ->assertSee('Pixel Art Painter');
});

test('the age filter chips narrow the arcade down', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Games::class)
        ->call('setFilter', KidsGameService::AGE_EARLY)
        ->assertSet('filter', KidsGameService::AGE_EARLY)
        ->assertSee('Number Catch')
        ->assertSee('Phonics Balloon Pop')
        ->assertDontSee('Math Runner')
        ->assertDontSee('Word Builder')
        ->call('setFilter', KidsGameService::AGE_EXPLORER)
        ->assertSet('filter', KidsGameService::AGE_EXPLORER)
        ->assertSee('Math Runner')
        ->assertSee('Word Builder')
        ->assertDontSee('Number Catch')
        ->assertDontSee('Phonics Balloon Pop');
});

test('games for every age stay visible under both age filters', function () {
    Livewire::actingAs($this->kidsUser);

    foreach ([KidsGameService::AGE_EARLY, KidsGameService::AGE_EXPLORER] as $filter) {
        Livewire::test(Games::class)
            ->call('setFilter', $filter)
            ->assertSee('Memory Match')
            ->assertSee('Coding Blocks')
            ->assertSee('Pixel Art Painter');
    }
});

test('unknown filters are ignored', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Games::class)
        ->call('setFilter', 'nonsense')
        ->assertSet('filter', KidsGameService::AGE_ALL);
});

/*
|--------------------------------------------------------------------------
| Tier gating on the dashboard
|--------------------------------------------------------------------------
*/

test('kids students see the games tab on their dashboard', function () {
    expect($this->kidsStudent->getAgeTier())->toBe('kids');

    Livewire::actingAs($this->kidsUser);

    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->call('setTab', 'games')
        ->assertSet('activeTab', 'games')
        ->assertSee('Game Arcade')
        ->assertSee('Number Catch')
        ->assertSee('Pixel Art Painter');
});

test('senior students never see the games tab', function () {
    expect($this->seniorStudent->getAgeTier())->toBe('senior');

    Livewire::actingAs($this->seniorUser);

    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->call('setTab', 'games')
        ->assertSet('activeTab', 'dashboard')
        ->assertDontSee('Number Catch')
        ->assertDontSee('Pixel Art Painter');
});

test('senior students cannot open the games tab through the open tab action', function () {
    Livewire::actingAs($this->seniorUser);

    Livewire::test(Dashboard::class)
        ->call('openTab', 'games')
        ->assertSet('activeTab', 'dashboard');
});

test('the games tab cannot be forced through the query string for non kids tiers', function () {
    Livewire::actingAs($this->seniorUser);

    Livewire::withQueryParams(['tab' => 'games'])
        ->test(Dashboard::class)
        ->assertSet('activeTab', 'dashboard');
});

test('the games tab can be opened through the query string for kids tiers', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::withQueryParams(['tab' => 'games'])
        ->test(Dashboard::class)
        ->assertSet('activeTab', 'games')
        ->assertSee('Game Arcade');
});

test('the games arcade renders inside its own livewire component', function () {
    Livewire::actingAs($this->kidsUser);

    $html = Livewire::test(Dashboard::class)
        ->call('setTab', 'games')
        ->html();

    // The games component must own its root element. If an earlier element
    // (a <link> or <style>) became the root, wire:click inside the arcade
    // would target the dashboard page instead of the games component.
    $root = strpos($html, 'wire:name="kids.games"');

    expect($root)->not->toBeFalse();

    expect(substr($html, $root, 160))->toContain('class="kg-root"');
});

test('the kids dashboard shows the arcade reward summary', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Dashboard::class)
        ->assertSee('Game Arcade')
        ->assertSee('Play games, learn new skills and collect stars!');
});

/*
|--------------------------------------------------------------------------
| Playing a game
|--------------------------------------------------------------------------
*/

test('every catalog game can be opened and renders a play screen', function () {
    Livewire::actingAs($this->kidsUser);

    foreach (array_keys(app(KidsGameService::class)->catalog()) as $key) {
        Livewire::test(Games::class)
            ->call('startGame', $key)
            ->assertSet('activeGame', $key)
            ->assertSuccessful();
    }
});

test('unknown games cannot be opened', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Games::class)
        ->call('startGame', 'does-not-exist')
        ->assertSet('activeGame', '');
});

test('finishing a game stores the best score, stars and play count', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Games::class)
        ->call('startGame', 'number-catch')
        ->assertSet('justFinished', false)
        ->call('finishGame', 12, 12)
        ->assertSet('justFinished', true)
        ->assertSet('lastCorrect', 12)
        ->assertSet('lastTotal', 12)
        ->assertSet('lastScore', 120)
        ->assertSet('lastStars', 3);

    $record = KidsGameScore::where('student_id', $this->kidsStudent->id)
        ->where('game_key', 'number-catch')
        ->firstOrFail();

    expect($record->best_score)->toBe(120)
        ->and($record->stars)->toBe(3)
        ->and($record->plays)->toBe(1)
        ->and($record->last_played_at)->not->toBeNull();
});

test('a weaker result never overwrites a better one', function () {
    Livewire::actingAs($this->kidsUser);

    $component = Livewire::test(Games::class)
        ->call('startGame', 'math-runner')
        ->call('finishGame', 10, 10);

    $component->call('playAgain')
        ->call('finishGame', 4, 10);

    $record = KidsGameScore::where('student_id', $this->kidsStudent->id)
        ->where('game_key', 'math-runner')
        ->firstOrFail();

    expect($record->best_score)->toBe(100)
        ->and($record->stars)->toBe(3)
        ->and($record->plays)->toBe(2);
});

test('reported answers are clamped to the number of rounds', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Games::class)
        ->call('startGame', 'memory-match')
        ->call('finishGame', 99, 10)
        ->assertSet('lastCorrect', 10)
        ->assertSet('lastStars', 3)
        ->call('playAgain')
        ->call('finishGame', -5, 10)
        ->assertSet('lastCorrect', 0)
        ->assertSet('lastStars', 0);
});

test('a result is only stored for a game that is actually open', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Games::class)
        ->call('finishGame', 5, 5)
        ->assertSet('justFinished', false);

    expect(KidsGameScore::count())->toBe(0);
});

test('the results screen shows the score and the stars earned', function () {
    Livewire::actingAs($this->kidsUser);

    Livewire::test(Games::class)
        ->call('startGame', 'coding-blocks')
        ->call('finishGame', 3, 3)
        ->assertSee('Perfect')
        ->assertSee('Back to Games')
        ->call('playAgain')
        ->call('finishGame', 1, 3)
        ->assertSee('Good try');
});

test('the games hub shows the stars and best score after playing', function () {
    Livewire::actingAs($this->kidsUser);

    $component = Livewire::test(Games::class)
        ->call('startGame', 'pixel-painter')
        ->call('finishGame', 9, 10);

    $component->call('backToHub')
        ->assertSet('activeGame', '')
        ->assertSee('Game Arcade')
        ->assertSee('Pixel Art Painter');

    $stats = $component->instance()->gameStats();

    expect($stats['pixel-painter']['stars'])->toBe(3)
        ->and($stats['pixel-painter']['plays'])->toBe(1)
        ->and($stats['pixel-painter']['best_score'])->toBe(90);
});

test('only the signed in students own scores are stored', function () {
    Livewire::actingAs($this->seniorUser);

    Livewire::test(Games::class)
        ->call('startGame', 'number-catch')
        ->call('finishGame', 3, 3);

    expect(KidsGameScore::where('student_id', $this->kidsStudent->id)->count())->toBe(0)
        ->and(KidsGameScore::where('student_id', $this->seniorStudent->id)->count())->toBe(1);
});

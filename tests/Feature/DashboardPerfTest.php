<?php

use App\Filament\Student\Pages\Dashboard;
use App\Models\User;
use Database\Seeders\PlatformSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PlatformSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('student'));
});

test('dashboard tab switch query profile', function () {
    $user = User::whereHas('student')->firstOrFail();

    $count = 0;
    DB::listen(function () use (&$count) {
        $count++;
    });

    $measure = function (callable $fn) use (&$count): array {
        $before = $count;
        $start = microtime(true);
        $fn();

        return [
            'queries' => $count - $before,
            'ms' => round((microtime(true) - $start) * 1000),
        ];
    };

    Livewire::actingAs($user);

    $results = [];
    $component = null;
    $results['initial mount'] = $measure(function () use (&$component) {
        $component = Livewire::test(Dashboard::class)->assertSuccessful();
    });

    foreach (['dashboard', 'classes', 'lessons', 'assignments', 'quizzes', 'grades', 'calendar', 'notifications', 'profile'] as $tab) {
        $results["switch -> {$tab}"] = $measure(function () use ($component, $tab) {
            $component->call('setTab', $tab)->assertSet('activeTab', $tab);
        });
    }

    dump($results);

    // Regression guard: every tab switch must stay within a small query budget.
    // (Baseline before batching/gating was 69-84 queries per switch.)
    foreach ($results as $label => $stats) {
        expect($stats['queries'])->toBeLessThanOrEqual(40);
    }
});

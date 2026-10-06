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

test('dashboard tab switch query callers', function () {
    $user = User::whereHas('student')->firstOrFail();

    $rows = [];
    DB::listen(function ($query) use (&$rows) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 40);
        $caller = '?';
        foreach ($trace as $frame) {
            $file = $frame['file'] ?? '';
            $line = $frame['line'] ?? 0;
            if (str_contains($file, 'vendor'.DIRECTORY_SEPARATOR.'laravel'.DIRECTORY_SEPARATOR.'framework')) {
                continue;
            }
            if (str_contains($file, 'vendor'.DIRECTORY_SEPARATOR.'doctrine')) {
                continue;
            }
            $caller = basename($file).':'.$line;
            break;
        }
        $sql = preg_replace('/\s+/', ' ', $query->sql);
        $rows[] = [$caller, substr($sql, 0, 110)];
    });

    Livewire::actingAs($user);
    $component = Livewire::test(Dashboard::class)->assertSuccessful();

    $rows = [];
    $component->call('setTab', 'lessons');

    $grouped = [];
    foreach ($rows as [$caller, $sql]) {
        $key = $caller.' || '.preg_replace('/\d+/', 'N', $sql);
        $grouped[$key] = ($grouped[$key] ?? 0) + 1;
    }
    arsort($grouped);
    foreach ($grouped as $key => $cnt) {
        dump("{$cnt}x  {$key}");
    }
    dump('total: '.count($rows));

    expect(true)->toBeTrue();
});

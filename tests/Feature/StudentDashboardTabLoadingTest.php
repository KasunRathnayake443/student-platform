<?php

use App\Filament\Student\Pages\Dashboard;
use App\Models\User;
use Database\Seeders\PlatformSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('student dashboard shows tab loading feedback for every age tier', function () {
    $this->travelTo(Carbon::parse('2026-11-07 12:00:00'));

    $this->seed(PlatformSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('student'));

    $tiers = [
        'student9@example.com' => 'kids',
        'student5@example.com' => 'junior',
        'student1@example.com' => 'senior',
    ];

    foreach ($tiers as $email => $tier) {
        $user = User::where('email', $email)->firstOrFail();
        Livewire::actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSuccessful()
            ->assertSet('tier', $tier)
            ->assertSee('student-tab-progress', false)
            ->assertSee('student-tab-loading-pill', false)
            ->assertSee('wire:loading.class="tabs-busy"', false)
            ->assertSee('tab-content-loading', false)
            ->call('setTab', 'lessons')
            ->assertSuccessful()
            ->assertSet('activeTab', 'lessons');
    }
});

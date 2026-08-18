<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    /**
     * Define environment setup for all tests — force bcrypt so that
     * Hash::make and the current_password rule always use the same algorithm.
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('hashing.driver', 'bcrypt');
        $app['config']->set('hashing.bcrypt.rounds', 4);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Purge the cached Hash driver so our config change takes effect.
        $this->app->forgetInstance('hash');
        $this->app->forgetInstance('hash.driver');
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}

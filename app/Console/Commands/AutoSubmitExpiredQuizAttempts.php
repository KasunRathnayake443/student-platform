<?php

namespace App\Console\Commands;

use App\Models\QuizAttempt;
use Illuminate\Console\Command;

class AutoSubmitExpiredQuizAttempts extends Command
{
    protected $signature = 'quizattempts:auto-submit';

    protected $description = 'Auto-submit any in-progress quiz attempts whose time has run out';

    public function handle(): int
    {
        $count = QuizAttempt::query()
            ->where('status', 'in_progress')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get()
            ->each(function (QuizAttempt $attempt): void {
                $attempt->autoSubmitIfExpired();
            });

        if ($count->isNotEmpty()) {
            $this->info("Auto-submitted {$count->count()} expired quiz attempt(s).");
        }

        return self::SUCCESS;
    }
}
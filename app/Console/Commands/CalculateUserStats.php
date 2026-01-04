<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StatsCalculationService;

class CalculateUserStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:calculate {--user_id= : Calculate stats for a specific user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate advanced climbing statistics for users';

    /**
     * Execute the console command.
     */
    public function handle(StatsCalculationService $statsService)
    {
        $userId = $this->option('user_id');

        if ($userId) {
            $this->info("Calculating stats for user {$userId}...");
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                $this->error("User {$userId} not found.");
                return 1;
            }

            $statsService->calculateStatsForUser($user);
            $this->info("Stats calculated successfully for user {$userId}.");
        } else {
            $this->info('Calculating stats for all users...');
            $statsService->calculateStatsForAllUsers();
            $this->info('Stats calculated successfully for all users.');
        }

        return 0;
    }
}

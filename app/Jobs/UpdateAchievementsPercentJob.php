<?php

namespace App\Jobs;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAchievementsPercentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $totalUsers = User::count();
        if ($totalUsers === 0) {
            Achievement::query()->update(['percent' => 0]);
            return;
        }
        foreach (Achievement::all() as $achievement) {
            $userCount = $achievement->users()->count();
            $percent = $userCount / $totalUsers;
            $achievement->percent = $percent;
            $achievement->save();
        }
    }
}

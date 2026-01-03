<?php

use App\Jobs\DeleteRoute;
use App\Jobs\SoftDeleteRoute;
use App\Models\Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;


Schedule::call(function () {
   foreach(Route::where('removing_at', '<', Carbon::now()->subYear()->toDateString())->get() as $route){
      DeleteRoute::dispatchSync($route);
   }
})->daily();

Schedule::call(function () {
   foreach(Route::where('removing_at', '<=', Carbon::now()->toDateString())->where('comment', '!=', 'softDeleted')->get() as $route){
      SoftDeleteRoute::dispatchSync($route);
   }
})->daily();

// Calculate user statistics nightly at 2 AM
Schedule::command('stats:calculate')->dailyAt('02:00');
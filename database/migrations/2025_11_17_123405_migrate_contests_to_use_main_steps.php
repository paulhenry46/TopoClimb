<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Contest;
use App\Models\ContestStep;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For each contest, create a main step if it doesn't have one
        $contests = Contest::all();
        
        foreach ($contests as $contest) {
            // Check if contest already has a step with order 0 named "Main"
            $mainStep = $contest->steps()->where('order', 0)->where('name', 'Main')->first();
            
            if (!$mainStep) {
                // Create a main step with contest dates
                $mainStep = ContestStep::create([
                    'contest_id' => $contest->id,
                    'name' => 'Main',
                    'order' => 0,
                    'start_time' => $contest->start_date,
                    'end_time' => $contest->end_date,
                ]);
                
                // Migrate routes from contest_route to contest_step_route with points
                $contestRoutes = \DB::table('contest_route')
                    ->where('contest_id', $contest->id)
                    ->get();
                
                foreach ($contestRoutes as $contestRoute) {
                    // Check if this route is not already in contest_step_route for this step
                    $exists = \DB::table('contest_step_route')
                        ->where('contest_step_id', $mainStep->id)
                        ->where('route_id', $contestRoute->route_id)
                        ->exists();
                    
                    if (!$exists) {
                        \DB::table('contest_step_route')->insert([
                            'contest_step_id' => $mainStep->id,
                            'route_id' => $contestRoute->route_id,
                            'points' => $contestRoute->points ?? 100,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove automatically created main steps
        ContestStep::where('name', 'Main')
            ->where('order', 0)
            ->delete();
    }
};

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class UserController extends Controller
{
    /**
     * Display the authenticated user.
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'birth_date' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|string|in:male,female,other',
        ]);

        $user = $request->user();
        $user->update($validated);

        return new UserResource($user);
    }

    public function stats(Request $request){

        $user = $request->user();

      $logs = Log::where('user_id', $user->id)->with('route.line.sector.area');
      $total = count(array_unique((clone $logs)->get()->pluck('route_id')->toArray()));

    $bouldering_logs = (clone $logs)->whereHas('route.line.sector.area', function ($query) {
        $query->where('type', 'bouldering');
    });

    $trad_logs = (clone $logs)->whereHas('route.line.sector.area', function ($query) {
        $query->where('type', 'trad');
    });


    $logs_b = $bouldering_logs->whereHas('route', function ($query) {
        $query->orderBy('grade', 'desc');
    })
    ->with('route')
    ->take(3)
    ->get();
    $route_b = $logs_b->pluck('route')->sortBy('grade')->first();

    $logs_t = $trad_logs->whereHas('route', function ($query) {
        $query->orderBy('grade', 'desc');
    })
    ->with('route')
    ->take(3)
    ->get();
    $route_t = $logs_t->pluck('route')->sortBy('grade')->first();
    
    
    if($route_b !== null and $route_b->defaultGradeFormated() !== null){
        $level_b = $route_b->defaultGradeFormated();
    }else{
        $level_b = array_key_first(config('climb.default_cotation.points'));
    }

    if($route_t !== null and $route_t->defaultGradeFormated() !== null){
        $level_t = $route_t->defaultGradeFormated();
    }else{
        $level_t = array_key_first(config('climb.default_cotation.points'));
    }


    // Group logs by route grade and count them
    $routesByGrade = $logs->get()->groupBy(function ($log) {
        return $log->route->defaultGradeFormated(); // Assuming `grade` is a column in the `routes` table
    })->map(function (Collection $group) {
        return $group->count();
    });



        return ['trad_level'=> $level_t, 
                'bouldering_level'=> $level_b, 
                'total_climbed' => $total,
                'routes_by_grade'=> $routesByGrade];
    }
}


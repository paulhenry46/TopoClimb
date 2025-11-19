<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContestResource;
use App\Models\Contest;
use App\Models\ContestStep;
use App\Models\Site;

class ContestController extends Controller
{
    /**
     * Display a listing of the resource for a site.
     */
    public function index(Site $site)
    {
        $contests = $site->contests;
        return ContestResource::collection($contests);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contest $contest)
    {
        return new ContestResource($contest);
    }

    /**
     * Return the steps of a contest with their routes (including pivot data & basic relations).
     */
    public function steps(Contest $contest)
    {
        // eager load nested relations to avoid N+1
        $contest->loadMissing('steps.routes');

        $steps = $contest->steps->map(function ($step) {
            return [
                'id' => $step->id,
                'name' => $step->name,
                'start_time' => $step->start_time,
                'end_time' => $step->end_time,
                'routes' => $step->routes->pluck('id')
            ];
        })->values();

        return response()->json(['steps' => $steps]);
    }

    public function rank(Contest $contest, int $step)
    {
        return response()->json(['rank' => $contest->getRankingForStep($step, true)]);
    }
    public function globalRank(Contest $contest)
    {
        return response()->json(['rank' => $contest->getRankingForStep(null, true)]);
    }
}

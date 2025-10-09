<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TeamResource;
use App\Models\Contest;
use App\Models\Team;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource for a contest.
     */
    public function index(Contest $contest)
    {
        $teams = $contest->teams;
        return TeamResource::collection($teams);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        return new TeamResource($team);
    }
}

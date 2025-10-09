<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContestResource;
use App\Models\Contest;
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
}

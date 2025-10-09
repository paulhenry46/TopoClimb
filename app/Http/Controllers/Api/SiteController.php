<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SiteResource;
use App\Models\Site;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::where('id', '!=', 1)->get();
        return SiteResource::collection($sites);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        return new SiteResource($site);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AreaResource;
use App\Http\Resources\Api\RouteResource;
use App\Models\Area;
use App\Models\Line;
use App\Models\Route;
use App\Models\Site;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource for a site.
     */
    public function index(Site $site)
    {
        $areas = $site->areas;
        return AreaResource::collection($areas);
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        return new AreaResource($area);
    }

    /**
     * Display a listing of routes for an area.
     */
    public function routes(Area $area)
    {
         $lines_id = Line::whereIn('sector_id', $area->sectors()->pluck('id'))->pluck('id');
        $routes = Route::whereIn('line_id', $lines_id)->where(function($query) {
            $query
                ->whereNull('removing_at')
                ->orWhere('removing_at', '>', now());
        })->get();


        return RouteResource::collection($routes);
    }
}

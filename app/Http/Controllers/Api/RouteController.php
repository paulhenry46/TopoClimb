<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RouteResource;
use App\Http\Resources\Api\LogResource;
use App\Models\Line;
use App\Models\Route;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource for a line.
     */
    public function index(Line $line)
    {
        $routes = $line->routes()->with(['users', 'tags'])->get();
        return RouteResource::collection($routes);
    }

    /**
     * Display the specified resource.
     */
    public function show(Route $route)
    {
        $route->load(['tags', 'users']);
        return new RouteResource($route);
    }

    public function logs(Route $route)
    {

        $logs = $route->logs()->with(['user'])->get();
        return LogResource::collection($logs);
    }
}

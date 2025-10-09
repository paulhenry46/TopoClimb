<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RouteResource;
use App\Models\Line;
use App\Models\Route;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource for a line.
     */
    public function index(Line $line)
    {
        $routes = $line->routes;
        return RouteResource::collection($routes);
    }

    /**
     * Display the specified resource.
     */
    public function show(Route $route)
    {
        return new RouteResource($route);
    }
}

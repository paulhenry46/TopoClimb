<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RouteResource;
use App\Http\Resources\Api\LogResource;
use App\Models\Line;
use App\Models\Route;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource for a line.
     */
    public function index(Line $line)
    {
        $routes = $line->routes()->with(['users', 'tags', 'logs'])->get();
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

    /**
     * Get logs for a route filtered by friends.
     */
    public function friendsLogs(Request $request, Route $route)
    {
        $user = $request->user();
        
        // Get all friends (both directions)
        $friends = $user->friends->merge($user->friendOf)->unique('id')->pluck('id');
        
        $logs = $route->logs()->whereIn('user_id', $friends)->with(['user'])->get();
        return LogResource::collection($logs);
    }

    /**
     * Store a new log for a route.
     */
    public function storeLog(Request $request, Route $route)
    {
        $validated = $request->validate([
            'comment' => 'nullable|string|max:1000',
            'video_url' => 'nullable|url|max:255',
            'grade' => 'required|integer|min:300|max:950',
            'type' => ['required', Rule::in(['work', 'flash', 'view'])],
            'way' => ['required', Rule::in(['top-rope', 'lead', 'bouldering'])],
        ]);

        $log = Log::create([
            'route_id' => $route->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'grade' => $validated['grade'],
            'type' => $validated['type'],
            'way' => $validated['way'],
        ]);

        $log->load('user');
        
        return (new LogResource($log))->response()->setStatusCode(201);
    }

    public function loggedRoutesByUser(Request $request)
    {
         $user = $request->user();

    // Get all logs for this user
    $logs = Log::where('user_id', $user->id)->get();

    // Get the route IDs from these logs
    $routeIds = $logs->pluck('route_id')->unique()->values();

    // Return as JSON
    return response()->json([
        'data' => $routeIds,
    ]);
        
    }

    /**
     * Get routes climbed by friends.
     */
    public function friendsRoutes(Request $request)
    {
        $user = $request->user();
        
        // Get all friends (both directions)
        $friends = $user->friends->merge($user->friendOf)->unique('id')->pluck('id');
        
        // Get logs from friends, ordered by most recent
        $logs = Log::whereIn('user_id', $friends)
                   ->with(['route.line.sector.area', 'user'])
                   ->orderByDesc('created_at')
                   ->take(10)
                   ->get();
        
        return LogResource::collection($logs);
    }
}

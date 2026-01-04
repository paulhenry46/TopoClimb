<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Jobs\GenerateQrCodeOfUser;
use App\Models\Log;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function stats(Request $request)
    {

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

        if ($route_b !== null and $route_b->defaultGradeFormated() !== null) {
            $level_b = $route_b->defaultGradeFormated();
        } else {
            $level_b = array_key_first(config('climb.default_cotation.points'));
        }

        if ($route_t !== null and $route_t->defaultGradeFormated() !== null) {
            $level_t = $route_t->defaultGradeFormated();
        } else {
            $level_t = array_key_first(config('climb.default_cotation.points'));
        }

        // Group logs by route grade and count them
        $routesByGrade = $logs->get()->groupBy(function ($log) {
            return $log->route->defaultGradeFormated(); // Assuming `grade` is a column in the `routes` table
        })->map(function (Collection $group) {
            return $group->count();
        });

        return ['trad_level' => $level_t,
            'bouldering_level' => $level_b,
            'total_climbed' => $total,
            'routes_by_grade' => $routesByGrade];
    }

    public function qrcode(Request $request)
    {
        $user = $request->user();
        $qrPath = 'qrcode/user-'.$user->id.'/qrcode.svg';

        if (Storage::exists($qrPath)) {
            return ['url' => Storage::url($qrPath)];
        } else {
            GenerateQrCodeOfUser::dispatchSync($user);

            return ['url' => Storage::url($qrPath)];
        }
    }

    /**
     * Get the authenticated user's friends.
     */
    public function friends(Request $request)
    {
        $user = $request->user();

        // Get both friends (user_id) and friendOf (friend_id) relationships
        $friends = $user->friends->merge($user->friendOf)->unique('id');

        return response()->json([
            'data' => $friends->map(function ($friend) {
                return [
                    'id' => $friend->id,
                    'name' => $friend->name,
                    'profile_photo_url' => $friend->profile_photo_url,
                ];
            })->values(),
        ]);
    }

    /**
     * Search users by name.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2|max:255',
        ]);

        $currentUser = $request->user();

        $users = User::where('name', 'LIKE', '%'.$validated['query'].'%')
            ->where('id', '!=', $currentUser->id)
            ->limit(10)
            ->get(['id', 'name', 'profile_photo_path']);

        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo_url' => $user->profile_photo_url,
                ];
            }),
        ]);
    }

    /**
     * Add a friend.
     */
    public function addFriend(Request $request)
    {
        $validated = $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $friendId = $validated['friend_id'];

        // Prevent adding yourself
        if ($user->id == $friendId) {
            return response()->json(['error' => 'You cannot add yourself as a friend'], 400);
        }

        // Check if already friends (either direction)
        $alreadyFriends = $user->friends()->where('friend_id', $friendId)->exists() ||
                         $user->friendOf()->where('user_id', $friendId)->exists();

        if ($alreadyFriends) {
            return response()->json(['error' => 'Already friends'], 400);
        }

        $user->friends()->attach($friendId);

        return response()->json([
            'message' => 'Friend added successfully',
            'data' => [
                'id' => $friendId,
            ],
        ], 201);
    }

    /**
     * Remove a friend.
     */
    public function removeFriend(Request $request, $friendId)
    {
        $user = $request->user();

        // Remove from both directions
        $user->friends()->detach($friendId);
        $user->friendOf()->detach($friendId);

        return response()->json([
            'message' => 'Friend removed successfully',
        ]);
    }

    /**
     * Get public profile for a user with stats.
     */
    public function publicProfile(User $user)
    {
        $logs = Log::where('user_id', $user->id)->where('is_public', true)->with('route.line.sector.area');
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

        if ($route_b !== null and $route_b->defaultGradeFormated() !== null) {
            $level_b = $route_b->defaultGradeFormated();
        } else {
            $level_b = array_key_first(config('climb.default_cotation.points'));
        }

        if ($route_t !== null and $route_t->defaultGradeFormated() !== null) {
            $level_t = $route_t->defaultGradeFormated();
        } else {
            $level_t = array_key_first(config('climb.default_cotation.points'));
        }

        // Group logs by route grade and count them
        $routesByGrade = $logs->get()->groupBy(function ($log) {
            return $log->route->defaultGradeFormated();
        })->map(function (Collection $group) {
            return $group->count();
        });

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_photo_url' => $user->profile_photo_url,
                'stats' => [
                    'trad_level' => $level_t,
                    'bouldering_level' => $level_b,
                    'total_climbed' => $total,
                    'routes_by_grade' => $routesByGrade,
                ],
            ],
        ]);
    }

    /**
     * Get complete user statistics.
     */
    public function completeStats(Request $request)
    {
        $user = $request->user();
        
        // Load user stats if they exist
        $userStats = $user->stats;
        
        if (!$userStats) {
            return response()->json([
                'message' => 'Statistics not yet calculated. They will be available after the nightly update.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => [
                // Technical Analysis
                'technical_analysis' => [
                    'consistency_variance' => [
                        'value' => $userStats->consistency_variance,
                        'description' => 'Measures how consistent you are in climbing level. Lower values indicate more stable performance.',
                    ],
                    'flash_work_ratio' => [
                        'value' => $userStats->flash_work_ratio,
                        'description' => 'Ratio of flash ascents to worked routes. Higher values indicate more explosive climbing style.',
                    ],
                    'risk_profile_abandonment_rate' => [
                        'value' => $userStats->risk_profile_abandonment_rate,
                        'description' => 'Percentage of attempted routes that were never completed.',
                    ],
                    'avg_difficulty_abandoned' => [
                        'value' => $userStats->avg_difficulty_abandoned,
                        'description' => 'Average difficulty level at which you tend to give up on routes.',
                    ],
                    'long_routes_count' => [
                        'value' => $userStats->long_routes_count,
                        'description' => 'Number of endurance-focused routes completed.',
                    ],
                    'short_routes_count' => [
                        'value' => $userStats->short_routes_count,
                        'description' => 'Number of power/boulder-focused routes completed.',
                    ],
                    'avg_time_between_attempts' => [
                        'value' => $userStats->avg_time_between_attempts,
                        'description' => 'Average time (in hours) between repeated attempts on the same route.',
                    ],
                    'movement_preferences' => [
                        'value' => $userStats->movement_preferences,
                        'description' => 'Your preferred movement types based on route tags.',
                    ],
                ],
                
                // Behavioral Analysis
                'behavioral_analysis' => [
                    'preferred_climbing_hour' => [
                        'value' => $userStats->preferred_climbing_hour,
                        'description' => 'Most common time of day for climbing.',
                    ],
                    'avg_session_duration' => [
                        'value' => $userStats->avg_session_duration,
                        'description' => 'Average duration (in hours) of your climbing sessions.',
                    ],
                    'avg_routes_per_session' => [
                        'value' => $userStats->avg_routes_per_session,
                        'description' => 'Typical number of routes climbed per session.',
                    ],
                    'exploration_ratio' => [
                        'value' => $userStats->exploration_ratio,
                        'description' => 'Percentage of climbing on new routes vs repeating routes.',
                    ],
                    'sector_fidelity' => [
                        'value' => $userStats->sector_fidelity,
                        'description' => 'Most frequently climbed sectors/areas.',
                    ],
                    'avg_attempts_before_success' => [
                        'value' => $userStats->avg_attempts_before_success,
                        'description' => 'Average number of attempts needed before successfully sending a route.',
                    ],
                    'project_count' => [
                        'value' => $userStats->project_count,
                        'description' => 'Number of routes worked across multiple sessions.',
                    ],
                ],
                
                // Progression Analysis
                'progression_analysis' => [
                    'progression_rate' => [
                        'value' => $userStats->progression_rate,
                        'description' => 'Grade progression per month. Positive values indicate improvement.',
                    ],
                    'plateau_detected' => [
                        'value' => $userStats->plateau_detected,
                        'description' => 'Whether stagnation is detected (true/false).',
                    ],
                    'plateau_weeks' => [
                        'value' => $userStats->plateau_weeks,
                        'description' => 'Number of weeks in plateau if detected.',
                    ],
                    'progression_by_style' => [
                        'value' => $userStats->progression_by_style,
                        'description' => 'Progression rate for different climbing styles (slab, overhang, vertical).',
                    ],
                    'progression_by_sector' => [
                        'value' => $userStats->progression_by_sector,
                        'description' => 'Progression rate in different sectors/areas.',
                    ],
                ],
                
                // Training Load Analysis
                'training_load_analysis' => [
                    'weekly_volume' => [
                        'value' => $userStats->weekly_volume,
                        'description' => 'Total climbing load in the past week.',
                    ],
                    'weekly_intensity' => [
                        'value' => $userStats->weekly_intensity,
                        'description' => 'Average difficulty level in the past week.',
                    ],
                    'acute_load' => [
                        'value' => $userStats->acute_load,
                        'description' => 'Training load in the last 7 days (short-term stress).',
                    ],
                    'chronic_load' => [
                        'value' => $userStats->chronic_load,
                        'description' => 'Average weekly training load over the last 4 weeks.',
                    ],
                    'acute_chronic_ratio' => [
                        'value' => $userStats->acute_chronic_ratio,
                        'description' => 'Ratio to prevent overtraining. Sweet spot: 0.8-1.3, >1.5 indicates high injury risk.',
                    ],
                    'overtraining_detected' => [
                        'value' => $userStats->overtraining_detected,
                        'description' => 'Automatic flag when acute/chronic ratio exceeds 1.5.',
                    ],
                    'avg_recovery_time' => [
                        'value' => $userStats->avg_recovery_time,
                        'description' => 'Average time (in hours) between climbing sessions.',
                    ],
                    'avg_time_between_performances' => [
                        'value' => $userStats->avg_time_between_performances,
                        'description' => 'Average time (in hours) between peak performances.',
                    ],
                ],
                
                'last_calculated_at' => $userStats->last_calculated_at,
            ],
        ]);
    }

    /**
     * Get last routes logged by a user (max 3).
     */
    public function publicRoutes(User $user)
    {
        $logs = Log::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        return \App\Http\Resources\Api\LogResource::collection($logs);
    }
}

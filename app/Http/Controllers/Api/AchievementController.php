<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AchievementResource;
use App\Models\Achievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    /**
     * Display a listing of all achievements.
     * This is a public endpoint.
     */
    public function index()
    {
        $achievements = Achievement::all();
        return AchievementResource::collection($achievements);
    }

    /**
     * Get the IDs of achievements unlocked by the authenticated user.
     * This is an authenticated endpoint.
     */
    public function userAchievements(Request $request)
    {
        $user = $request->user();
        $achievements = $user->achievements()->withPivot('unlocked_at')->get();
        $result = [];
        foreach ($achievements as $achievement) {
            $result[$achievement->id] = $achievement->pivot->unlocked_at;
        }
        return response()->json(['data'=>$result]);
    }
}

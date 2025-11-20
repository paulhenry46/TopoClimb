<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Jobs\GenerateQrCodeOfUser;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
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

    public function stats(Request $request){

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
        
        
        if($route_b !== null and $route_b->defaultGradeFormated() !== null){
            $level_b = $route_b->defaultGradeFormated();
        }else{
            $level_b = array_key_first(config('climb.default_cotation.points'));
        }

        if($route_t !== null and $route_t->defaultGradeFormated() !== null){
            $level_t = $route_t->defaultGradeFormated();
        }else{
            $level_t = array_key_first(config('climb.default_cotation.points'));
        }


        // Group logs by route grade and count them
        $routesByGrade = $logs->get()->groupBy(function ($log) {
            return $log->route->defaultGradeFormated(); // Assuming `grade` is a column in the `routes` table
        })->map(function (Collection $group) {
            return $group->count();
        });



        return ['trad_level'=> $level_t, 
                'bouldering_level'=> $level_b, 
                'total_climbed' => $total,
                'routes_by_grade'=> $routesByGrade];
    }

    
    public function qrcode(Request $request){
        $user = $request->user();
        $qrPath = 'qrcode/user-' . $user->id . '/qrcode.svg';

        if (Storage::exists($qrPath)) {
            return ['url' => Storage::url($qrPath)];
        }else{
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
            })->values()
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
        
        $users = User::where('name', 'LIKE', '%' . $validated['query'] . '%')
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
            })
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
            ]
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
            'message' => 'Friend removed successfully'
        ]);
    }

}


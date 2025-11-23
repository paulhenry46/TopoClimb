<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ContestController extends Controller
{
    public function join(Site $site, Contest $contest, string $token)
    {
        $team = $contest->teams()->where('invitation_token', $token)->firstOrFail();
            
            // Check if user is already in a team
            $userTeam = $contest->teams()
                ->whereHas('users', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();
            
            if ($userTeam) {
                return redirect()->route('contest.my-team', ['site' => $site->slug, 'contest' => $contest->id])
                    ->with('error', __('You are already in a team for this contest.'));
            }

            // Check if team is full
            if ($team->isFull()) {
                return redirect()->route('contest.my-team', ['site' => $site->slug, 'contest' => $contest->id])
                    ->with('error', __('This team is full.'));
            }

            // Add user to team
            $team->users()->syncWithoutDetaching([auth()->id()]);

            return redirect()->route('contest.my-team', ['site' => $site->slug, 'contest' => $contest->id])
                ->with('success', __('You have successfully joined the team!'));
    }

}
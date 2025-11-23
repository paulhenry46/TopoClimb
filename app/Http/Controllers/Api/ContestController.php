<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContestCategoryResource;
use App\Http\Resources\Api\ContestResource;
use App\Models\Contest;
use App\Models\ContestCategory;
use App\Models\Site;
use Illuminate\Http\Request;

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

    /**
     * Return the steps of a contest with their routes (including pivot data & basic relations).
     */
    public function steps(Contest $contest)
    {
        // eager load nested relations to avoid N+1
        $contest->loadMissing('steps.routes');

        $steps = $contest->steps->map(function ($step) {
            return [
                'id' => $step->id,
                'name' => $step->name,
                'start_time' => $step->start_time,
                'end_time' => $step->end_time,
                'routes' => $step->routes->pluck('id'),
            ];
        })->values();

        return response()->json(['steps' => $steps]);
    }

    public function rank(Contest $contest, int $step)
    {
        return response()->json(['rank' => $contest->getRankingForStep($step, true)]);
    }

    public function globalRank(Contest $contest)
    {
        return response()->json(['rank' => $contest->getRankingForStep(null, true)]);
    }

    /**
     * Get all categories for a contest.
     */
    public function categories(Contest $contest)
    {
        $categories = $contest->categories;

        return ContestCategoryResource::collection($categories);
    }

    /**
     * Get the ranking for a specific category in a contest.
     */
    public function categoryRank(Contest $contest, ContestCategory $category)
    {
        // Verify the category belongs to the contest
        if ($category->contest_id !== $contest->id) {
            return response()->json(['error' => 'Category does not belong to this contest'], 404);
        }

        return response()->json(['rank' => $contest->getCategoryRankings($category->id, null, true)]);
    }

    /**
     * Get the ranking for a specific category in a contest step.
     */
    public function categoryStepRank(Contest $contest, ContestCategory $category, int $step)
    {
        // Verify the category belongs to the contest
        if ($category->contest_id !== $contest->id) {
            return response()->json(['error' => 'Category does not belong to this contest'], 404);
        }

        return response()->json(['rank' => $contest->getCategoryRankings($category->id, $step, true)]);
    }

    /**
     * Get the categories a user belongs to for a contest.
     */
    public function userCategories(Contest $contest, Request $request)
    {
        $user = $request->user();

        $categories = $contest->categories()
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return ContestCategoryResource::collection($categories);
    }

    /**
     * Register a user for a category.
     */
    public function registerForCategory(Contest $contest, ContestCategory $category, Request $request)
    {
        $user = $request->user();

        // Verify the category belongs to the contest
        if ($category->contest_id !== $contest->id) {
            return response()->json(['error' => 'Category does not belong to this contest'], 404);
        }

        // Check if category is in auto-assign mode
        if ($category->auto_assign) {
            return response()->json(['error' => 'Cannot manually register for auto-assign categories'], 403);
        }

        // Register user for category if not already registered
        $category->users()->syncWithoutDetaching([$user->id]);

        return response()->json(['message' => 'Successfully registered for category'], 200);
    }

    /**
     * Unregister a user from a category.
     */
    public function unregisterFromCategory(Contest $contest, ContestCategory $category, Request $request)
    {
        $user = $request->user();

        // Verify the category belongs to the contest
        if ($category->contest_id !== $contest->id) {
            return response()->json(['error' => 'Category does not belong to this contest'], 404);
        }

        // Check if category is in auto-assign mode
        if ($category->auto_assign) {
            return response()->json(['error' => 'Cannot manually unregister from auto-assign categories'], 403);
        }

        // Unregister user from category
        $category->users()->detach($user->id);

        return response()->json(['message' => 'Successfully unregistered from category'], 200);
    }
}

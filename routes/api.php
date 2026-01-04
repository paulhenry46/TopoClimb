<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContestController;
use App\Http\Controllers\Api\LineController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public endpoints (no authentication required)
Route::prefix('v1')->group(function () {
    Route::get('/meta', [AuthController::class, 'meta']);
    // Sites
    Route::get('/sites', [SiteController::class, 'index']);
    Route::get('/sites/{site}', [SiteController::class, 'show']);

    // Areas
    Route::get('/sites/{site}/areas', [AreaController::class, 'index']);
    Route::get('/areas/{area}', [AreaController::class, 'show']);
    Route::get('/areas/{area}/routes', [AreaController::class, 'routes']);

    // Sectors
    Route::get('/areas/{area}/sectors', [SectorController::class, 'index']);
    Route::get('/areas/{area}/schemas', [AreaController::class, 'sectorsSchema']);
    Route::get('/sectors/{sector}', [SectorController::class, 'show']);

    // Lines
    Route::get('/sectors/{sector}/lines', [LineController::class, 'index']);
    Route::get('/lines/{line}', [LineController::class, 'show']);

    // Routes
    Route::get('/lines/{line}/routes', [RouteController::class, 'index']);
    Route::get('/routes/{route}', [RouteController::class, 'show']);
    Route::get('/routes/{route}/logs', [RouteController::class, 'logs']);
    Route::get('/routes/{route}/logs/friends', [RouteController::class, 'friendsLogs'])->middleware('auth:sanctum');

    // Public User endpoints
    Route::get('/users/{user}', [UserController::class, 'publicProfile'])->whereNumber('user');
    Route::get('/users/{user}/routes', [UserController::class, 'publicRoutes'])->whereNumber('user');

    // Current Events
    Route::get('/current_events', [ContestController::class, 'currentEvents']);

    // Contests
    Route::get('/sites/{site}/contests', [ContestController::class, 'index']);
    Route::get('/contests/{contest}', [ContestController::class, 'show']);
    Route::get('/contests/{contest}/rank', [ContestController::class, 'globalRank']);
    Route::get('/contests/{contest}/steps', [ContestController::class, 'steps']);
    Route::get('/contests/{contest}/steps/{step}/rank', [ContestController::class, 'rank']);
    Route::get('/contests/{contest}/categories', [ContestController::class, 'categories']);
    Route::get('/contests/{contest}/categories/{category}/rank', [ContestController::class, 'categoryRank']);
    Route::get('/contests/{contest}/categories/{category}/steps/{step}/rank', [ContestController::class, 'categoryStepRank']);

    // Teams
    Route::get('/contests/{contest}/teams', [TeamController::class, 'index']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);

    // Tags
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{tag}', [TagController::class, 'show']);

    // Achievements (public)
    Route::get('/achievements', [AchievementController::class, 'index']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Route logs
        Route::post('/routes/{route}/logs/create', [RouteController::class, 'storeLog']);
        Route::get('/user/logs', [RouteController::class, 'loggedRoutesByUser']);
        Route::get('/user/logs/friends', [RouteController::class, 'friendsRoutes']);
        Route::get('/user/stats', [UserController::class, 'stats']);
        Route::get('/user/trainingStats', [UserController::class, 'trainingStats']);
        Route::get('/user/qrcode', [UserController::class, 'qrcode']);
        Route::post('/user/update', [UserController::class, 'update']);

        // Friends
        Route::get('/user/friends', [UserController::class, 'friends']);
        Route::get('/users/search', [UserController::class, 'search']);
        Route::post('/user/friends', [UserController::class, 'addFriend']);
        Route::delete('/user/friends/{friendId}', [UserController::class, 'removeFriend']);

        // Contest categories
        Route::get('/contests/{contest}/user/categories', [ContestController::class, 'userCategories']);
        Route::post('/contests/{contest}/categories/{category}/register', [ContestController::class, 'registerForCategory']);
        Route::delete('/contests/{contest}/categories/{category}/unregister', [ContestController::class, 'unregisterFromCategory']);

        // User Achievements (authenticated)
        Route::get('/user/achievements', [AchievementController::class, 'userAchievements']);

    });
});

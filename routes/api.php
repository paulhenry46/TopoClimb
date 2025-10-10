<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\ContestController;
use App\Http\Controllers\Api\LineController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public endpoints (no authentication required)
Route::prefix('v1')->group(function () {
    // Sites
    Route::get('/sites', [SiteController::class, 'index']);
    Route::get('/sites/{site}', [SiteController::class, 'show']);
    
    // Areas
    Route::get('/sites/{site}/areas', [AreaController::class, 'index']);
    Route::get('/areas/{area}', [AreaController::class, 'show']);
    
    // Sectors
    Route::get('/areas/{area}/sectors', [SectorController::class, 'index']);
    Route::get('/sectors/{sector}', [SectorController::class, 'show']);
    
    // Lines
    Route::get('/sectors/{sector}/lines', [LineController::class, 'index']);
    Route::get('/lines/{line}', [LineController::class, 'show']);
    
    // Routes
    Route::get('/lines/{line}/routes', [RouteController::class, 'index']);
    Route::get('/routes/{route}', [RouteController::class, 'show']);
    
    // Contests
    Route::get('/sites/{site}/contests', [ContestController::class, 'index']);
    Route::get('/contests/{contest}', [ContestController::class, 'show']);
    
    // Teams
    Route::get('/contests/{contest}/teams', [TeamController::class, 'index']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    
    // Tags
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{tag}', [TagController::class, 'show']);
});

// Authenticated endpoints (require API token)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // User profile
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
});

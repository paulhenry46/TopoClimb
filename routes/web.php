<?php

use App\Models\Area;
use App\Models\Route as ModelsRoute;
use App\Models\Site;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('/admin/sites')->name('admin.')->group(function () {
        Route::get('/', function () {
            return view('sites.index');
        })->name('sites.manage');
        
        Route::prefix('/{site}')->group(function () {
            Route::get('/', function (Site $site) {
                return view('areas.index', compact('site'));
            })->name('areas.manage');
    
            Route::get('/view', function (Site $site) {
                return view('areas.view', compact('site'));
            })->name('area.view');
            
            Route::prefix('/areas/{area}')->group(function () {
                Route::get('/', function (Site $site, Area $area) {
                    return view('sectors.index', compact('site', 'area'));
                })->name('sectors.manage');
                Route::prefix('/initialize')->group(function () {
                    Route::get('/map', function (Site $site, Area $area) {
                        return view('areas.initialize.step-1', compact('site', 'area'));
                    })->name('areas.initialize');
                    Route::get('/sectors', function (Site $site, Area $area) {
                        return view('areas.initialize.step-2', compact('site', 'area'));
                    })->name('areas.initialize.sectors');
                    Route::get('/lines', function (Site $site, Area $area) {
                        return view('areas.initialize.step-3', compact('site', 'area'));
                    })->name('areas.initialize.lines');
                });
                Route::prefix('/routes')->group(function () {
                    Route::get('/new', function (Site $site, Area $area) {
                        return view('routes.edit-infos', compact('site', 'area'));
                    })->name('routes.new');
                    Route::get('/{route}/path', function (Site $site, Area $area, ModelsRoute $route) {
                        return view('routes.edit-path', compact('site', 'area', 'route'));
                    })->name('routes.path');
                    Route::get('/{route}/photo', function (Site $site, Area $area, ModelsRoute $route) {
                        return view('routes.edit-photo', compact('site', 'area', 'route'));
                    })->name('routes.photo');
                    Route::get('/{route}/photo/circle', function (Site $site, Area $area, ModelsRoute $route) {
                        return view('routes.edit-photo-circle', compact('site', 'area', 'route'));
                    })->name('routes.circle');
                });
            });
        });
    });
});

Route::prefix('/sites/{site:slug}')->group(function () {

    Route::get('/view', function (Site $site) {
        return view('sites.view', compact('site'));
    })->name('site.view');

    Route::prefix('/{area:slug}')->scopeBindings()->group(function () {

        Route::get('/view', function (Site $site, Area $area) {
            return view('areas.view', compact('site', 'area'));
        })->name('area.view');
    
    });

});
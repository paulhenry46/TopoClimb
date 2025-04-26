<?php

use App\Models\Area;
use App\Models\Route as ModelsRoute;
use App\Models\Site;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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
                Route::prefix('/topo')->group(function () {
                    Route::get('/map', function (Site $site, Area $area) {
                        return view('topo.wizard', compact('site', 'area'));
                    })->name('areas.topo.initialize');

                    Route::get('/result/routes', function (Site $site, Area $area) {
                        return view('topo.pdf.routes', compact('site', 'area'));
                    })->name('areas.topo.result.routes');
                    Route::get('/result/map', function (Site $site, Area $area) {
                        return view('topo.pdf.map', compact('site', 'area'));
                    })->name('areas.topo.result.map');
                    
                    Route::get('/sectors', function (Site $site, Area $area) {
                        return view('areas.initialize.step-2', compact('site', 'area'));
                    })->name('areas.topo.initialize.sectors');
                    Route::get('/lines', function (Site $site, Area $area) {
                        return view('areas.initialize.step-3', compact('site', 'area'));
                    })->name('areas.topo.initialize.lines');
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
                    Route::get('/{route}/finish', function (Site $site, Area $area, ModelsRoute $route) {
                        return view('routes.finish-wizard', compact('site', 'area', 'route'));
                    })->name('routes.finish');
                });
            });
        });
    });
});

Route::prefix('/sites/{site:slug}')->group(function () {

    Route::get('/', function (Site $site) {
        return view('sites.view', compact('site'));
    })->name('site.view');

    Route::prefix('/{area:slug}')->scopeBindings()->group(function () {

        Route::get('/', function (Site $site, Area $area) {
            return view('areas.view', compact('site', 'area'));
        })->name('area.view');
    
    });

});

Route::get('/empty/photo/{color}.svg', function (string $color) {
    $colors = [
        'red' => '#fca5a5',
        'blue' => '#93c5fd',
        'green' => '#86efac',
        'yellow' => '#fde047',
        'purple' => '#d8b4fe',
        'pink' => '#f9a8d4',
        'gray' => '#d1d5db',
        'black' => '#000000',
        'white' => '#ffffff',
        'emerald' => '#6ee7b7',
        'orange' => '#fdba74'
    ];

    $content = str_replace('color', $colors[$color], Storage::get('photos/blank.svg'));

$response = response()->make($content, 200);
$response->header('Content-Type', 'image/svg+xml');
return $response;
})->name('empty.photo');
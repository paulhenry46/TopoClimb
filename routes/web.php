<?php

use App\Models\Area;
use App\Models\Route as ModelsRoute;
use App\Models\Site;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\GoogleController;

Route::get('/', function(){
    return redirect(route('sites.public-index'));
})->name('welcome');


Route::controller(GoogleController::class)->group(function(){
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback');
});

Route::middleware([
    'auth:web',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('/admin/users')->group(function () {
        Route::get('/', function (Site $site) {
            return view('users.index');
        })->middleware('can:users, site')->name('admin.users');
    });

    Route::prefix('/admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return view('sites.index');
        })->middleware('can:sites')->name('sites.manage');
        
        Route::prefix('/site/{site}')->group(function () {
            Route::get('/', function (Site $site) {
                return view('areas.index', compact('site'));
            })->middleware('can:edit_areas,site')->name('areas.manage');

            Route::get('/stats', function (Site $site) {
                return view('sites.stats', compact('site'));
            })->middleware('can:edit_areas,site')->name('site.stats');
            
            Route::prefix('/areas/{area}')->group(function () {
                Route::get('/', function (Site $site, Area $area) {
                    return view('sectors.index', compact('site', 'area'));
                })->middleware('can:edit_areas,site')->name('sectors.manage');
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
                })->middleware('can:edit_areas,site');

                Route::prefix('/topo')->group(function () {
    
                    Route::prefix('/map')->name('areas.topo.initialize.')->group(function () {
                        Route::get('/sectors', function (Site $site, Area $area) {
                            $type = 'sectors';
                            return view('topo.wizard', compact('site', 'area', 'type'));
                        })->name('sectors');
                        Route::get('/lines', function (Site $site, Area $area) {
                            $type = 'lines';
                            return view('topo.wizard', compact('site', 'area', 'type'));
                        })->name('lines');
                        Route::get('/schema', function (Site $site, Area $area) {
                            $type = 'schema';
                            return view('topo.wizard', compact('site', 'area', 'type'));
                        })->name('schema');
                    });

                    Route::prefix('/result/map')->name('areas.topo.result.map.')->group(function () {
                        Route::get('/sectors', function (Site $site, Area $area) {
                            $type = 'sectors';
                            return view('topo.pdf.map', compact('site', 'area', 'type'));
                        })->name('sectors');
                        Route::get('/lines', function (Site $site, Area $area) {
                            $type = 'lines';
                            return view('topo.pdf.map', compact('site', 'area', 'type'));
                        })->name('lines');
                        Route::get('/schema', function (Site $site, Area $area) {
                            $type = 'schema';
                            return view('topo.pdf.map', compact('site', 'area', 'type'));
                        })->name('schema');
                    });

                    Route::prefix('/result/routes')->name('areas.topo.result.routes.')->group(function () {
                        Route::get('/sectors', function (Site $site, Area $area) {
                            $type = 'sectors';
                            return view('topo.pdf.routes', compact('site', 'area', 'type'));
                        })->name('sectors');
                        Route::get('/lines', function (Site $site, Area $area) {
                            $type = 'lines';
                            return view('topo.pdf.routes', compact('site', 'area', 'type'));
                        })->name('lines');
                        Route::get('/schema', function (Site $site, Area $area) {
                            $type = 'schema';
                            return view('topo.pdf.routes', compact('site', 'area', 'type'));
                        })->name('schema');
                    });

                    Route::get('/tags', function (Site $site, Area $area) {
                        return view('topo.pdf.tags', compact('site', 'area'));
                    })->name('areas.topo.tags');
                   
                })->middleware('can:edit,site');

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
                })->middleware('can:edit_routes,site');
            });
        });
    });
});

Route::get('r/{route}', function(ModelsRoute $route){
    $area = $route->line->sector->area;
    return redirect(route('site.area.view', ['site' => $area->site->slug, 'area' => $area->slug, 'route_id' => $route->id]));
})->name('route.shortUrl');

Route::get('/sites', function () {
    return view('sites.index-public', ['sites'=> Site::where('id', '!=', 1)->get()]);
})->name('sites.public-index');

Route::prefix('/sites/{site:slug}')->group(function () {

    Route::get('/', function (Site $site) {
        return view('sites.view', compact('site'));
    })->name('site.view');

    Route::prefix('/{area:slug}')->scopeBindings()->group(function () {

        Route::get('/', function (Site $site, Area $area) {
            return view('areas.view', compact('site', 'area'));
        })->name('site.area.view');
    });
});

Route::get('/empty/photo/{color}.svg', function (string $color) {
    $colors = [
        'red' => '#ef4444',
        'blue' => '#3b82f6',
        'green' => '#22c55e',
        'yellow' => '#fde047',
        'purple' => '#d8b4fe',
        'pink' => '#f9a8d4',
        'gray' => '#d1d5db',
        'black' => '#000000',
        'white' => '#ffffff',
        'emerald' => '#6ee7b7',
        'orange' => '#fdba74',
        'amber' => '#fbbf24',
        'teal' => '#00bba7',
        'lime' => '#7ccf00',
        'cyan' => '#00b8db',
        'sky' => '#00a6f4',
        'indigo' => '#615fff',
        'violet' => '#8e51ff',
        'fuchsia' => '#d946ef',
        'rose' => '#f43f5e',
        'slate' => '#64748b',
        'gray' => '#6b7280',
        'zinc' => '#71717a'
    ];
    $content = str_replace('color', $colors[$color], Storage::get('photos/blank.svg'));

$response = response()->make($content, 200);
$response->header('Content-Type', 'image/svg+xml');
return $response;
})->name('empty.photo');
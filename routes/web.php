<?php

use App\Models\Area;
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
});


Route::get('/sites/manage', function () {
    return view('sites.index');
})->name('sites.manage');

Route::get('sites/{site}/areas/manage', function (Site $site) {
    return view('areas.index', compact('site'));
})->name('areas.manage');

Route::get('sites/{site}/areas/{area}/initialize', function (Site $site, Area $area) {
    return view('areas.initialize', compact('site', 'area'));
})->name('areas.initialize');
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AreaResource;
use App\Http\Resources\Api\RouteResource;
use App\Models\Area;
use App\Models\Line;
use App\Models\Route;
use App\Models\Site;
use Illuminate\Support\Facades\Storage;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource for a site.
     */
    public function index(Site $site)
    {
        $areas = $site->areas;
        return AreaResource::collection($areas);
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        return new AreaResource($area);
    }

    /**
     * Display a listing of routes for an area.
     */
    public function routes(Area $area)
    {
         $lines_id = Line::whereIn('sector_id', $area->sectors()->pluck('id'))->pluck('id');
        $routes = Route::whereIn('line_id', $lines_id)->where(function($query) {
            $query
                ->whereNull('removing_at')
                ->orWhere('removing_at', '>', now());
        })->get();


        return RouteResource::collection($routes);
    }

    public function sectorsSchema(Area $area){
        if($area->type !== 'bouldering'){
            $site = $area->site;
        $schema_data['data'] = [];
        $schema_data['sectors'] = [];
                foreach ($area->sectors as $sector) {
                    if(Storage::exists('paths/site-'.$site->id.'/area-'.$area->id.'/sector-'.$sector->id.'/edited/android.svg')){
                         $data = ['id' => $sector->id,
                    'name' => $sector->name,
                   'paths' => Storage::url('paths/site-'.$site->id.'/area-'.$area->id.'/sector-'.$sector->id.'/edited/android.svg'),
                   'bg' => Storage::url('plans/site-'.$site->id.'/area-'.$area->id.'/sector-'.$sector->id.'/schema')
                  ];
                    }else{
                         $data = ['id' => $sector->id,
                    'name' => $sector->name,
                   'paths' => null,
                   'bg' => null
                  ];
                    }
         
            array_push($schema_data['data'], $data);
          }
          return $schema_data['data'];
        }else{
        return ['error' => 'Error : Bouldering Area'];
        }
}
}

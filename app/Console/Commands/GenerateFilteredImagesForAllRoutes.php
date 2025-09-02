<?php

namespace App\Console\Commands;

use App\Jobs\ImageFilter;
use App\Models\Route;
use Illuminate\Console\Command;

class GenerateFilteredImagesForAllRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-filtered-images-for-all-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach(Route::where('id', '!=', 1)->where('id', '!=', 2)->with('line.sector.area.site')->get() as $route){
            $area = $route->line->sector->area;
            $site = $area->site;
             $path = 'photos/site-'.$site->id.'/area-'.$area->id.'/route-'.$route->id;
            $filtered_path = 'photos/site-'.$site->id.'/area-'.$area->id.'/route-filtered-'.$route->id;
            ImageFilter::dispatchSync($route->color, $path, $filtered_path);
            $this->info('route OK'.$route->id);
        }   
    }
}

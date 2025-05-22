<?php

namespace App\Console\Commands;

use App\Jobs\GenerateQRCodeOfRoute;
use App\Jobs\GenerateQrCodeOfSite;
use App\Models\Route;
use App\Models\Site;
use Illuminate\Console\Command;

class GenerateQRCodeForAllRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-qrcode-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR code for all routes. usefull if url of website has changed or after a migration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting QR code generation for all routes...');

        // Fetch all routes
        $routes = Route::with(['line.sector.area.site'])->with(['line.sector.area'])->get();

        foreach ($routes as $route) {
                $area = $route->line->sector->area ?? null;
                $site = $area->site ?? null;

                GenerateQRCodeOfRoute::dispatchSync($route, $area, $site);
                $this->info("QR code generated for route: {$route->name} (ID: {$route->id})");
            
        }

        foreach (Site::all() as $site) {

            GenerateQrCodeOfSite::dispatchSync($site);
            $this->info("QR code generated for site: {$site->name} (ID: {$site->id})");
        }

        $this->info('QR code generation completed for all routes.');
    
    }
}

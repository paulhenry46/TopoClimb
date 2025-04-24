<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Route;
use App\Models\Site;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class GenerateQRCodeOfRoute implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Route $route, public Area $area, public Site $site)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $url = 'priut';
        $file = Storage::path('qrcode/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg');
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $writer->writeFile($url, $file);
    }
}

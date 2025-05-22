<?php

namespace App\Jobs;

use App\Models\Site;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateQrCodeOfSite implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Site $site)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $url = route('site.view', ['site' => $this->site->slug]);
        $directory = 'qrcode/site-'.$this->site->id;
        $file = Storage::path('qrcode/site-'.$this->site->id.'/qrcode.svg');
        
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }
        
        $renderer = new ImageRenderer(
            new RendererStyle(50),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $writer->writeFile($url, $file);
    }
}

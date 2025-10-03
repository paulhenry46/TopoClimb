<?php

namespace App\Jobs;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateQrCodeOfUser implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate a URL that encodes the user ID
        $url = route('user.qr', ['user' => $this->user->id]);
        $directory = 'qrcode/user-' . $this->user->id;
        $file = Storage::path('qrcode/user-' . $this->user->id . '/qrcode.svg');
        
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }
        
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $writer->writeFile($url, $file);
    }
}

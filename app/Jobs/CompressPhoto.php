<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class CompressPhoto implements ShouldQueue
{
    use Queueable;

    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB in bytes

    /**
     * Create a new job instance.
     */
    public function __construct(public string $photo_path) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = Storage::path($this->photo_path);

        // Check if file exists
        if (! Storage::exists($this->photo_path)) {
            logger()->error('Photo not found for compression: '.$this->photo_path);

            return;
        }

        $fileSize = Storage::size($this->photo_path);

        // Only compress if file is larger than 2MB
        if ($fileSize <= self::MAX_FILE_SIZE) {
            return;
        }

        // Load the image
        $image = @imagecreatefromjpeg($fullPath);

        if ($image === false) {
            logger()->error('Failed to load image for compression: '.$this->photo_path);

            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Start with quality 85 and reduce until file size is acceptable
        $quality = 85;
        $tempPath = $fullPath.'.tmp';

        while ($quality >= 50) {
            // Save with current quality
            imagejpeg($image, $tempPath, $quality);

            $compressedSize = filesize($tempPath);

            // If file size is acceptable, replace original
            if ($compressedSize <= self::MAX_FILE_SIZE) {
                imagedestroy($image);

                // Delete original and rename temp file
                Storage::delete($this->photo_path);
                rename($tempPath, $fullPath);

                logger()->info("Photo compressed from {$fileSize} to {$compressedSize} bytes at quality {$quality}: {$this->photo_path}");

                return;
            }

            // Reduce quality for next iteration
            $quality -= 5;
        }

        // If we couldn't compress enough with quality reduction alone,
        // save at quality 50 as the best we can do
        imagejpeg($image, $tempPath, 50);
        imagedestroy($image);

        $finalSize = filesize($tempPath);
        Storage::delete($this->photo_path);
        rename($tempPath, $fullPath);

        logger()->info("Photo compressed from {$fileSize} to {$finalSize} bytes at minimum quality 50: {$this->photo_path}");
    }
}

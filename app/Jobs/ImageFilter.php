<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ImageFilter implements ShouldQueue
{
    use Queueable;
    private array $colorToInterval;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $color, public string $picture_path, public string $filtered_path)
    {
        $this->colorToInterval = config('climb.colorsInterval')[$this->color];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        //$image = imagecreatefromjpeg($this->picture);
        $image = imagecreatefromjpeg(Storage::path($this->picture_path));
        $width = imagesx($image);
        $height = imagesy($image);

        if($this->color == 'red'){
            $upper[0] = $this->colorToInterval[0][1];
            $upper[1] = $this->colorToInterval[1][1];
            $lower[0] = $this->colorToInterval[0][0];
            $lower[1] = $this->colorToInterval[1][0];

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    list($h, $s, $v) = $this->rgbToHsv($r, $g, $b);

                    // check range
                    if ( ($h >= $lower[0][0] && $h <= $upper[0][0] &&
                        $s >= $lower[0][1] && $s <= $upper[0][1] &&
                        $v >= $lower[0][2] && $v <= $upper[0][2])or
                        $h >= $lower[1][0] && $h <= $upper[1][0] &&
                        $s >= $lower[1][1] && $s <= $upper[1][1] &&
                        $v >= $lower[1][2] && $v <= $upper[1][2]) {
                        continue; // Keep color
                    } else {
                        // Convert to grayscale
                        $gray = intval(($r + $g + $b) / 3);
                        $gray_color = imagecolorallocate($image, $gray, $gray, $gray);
                        imagesetpixel($image, $x, $y, $gray_color);
                    }
                }
            }
        }else{
            $upper = $this->colorToInterval[1];
            $lower = $this->colorToInterval[0];

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    list($h, $s, $v) = $this->rgbToHsv($r, $g, $b);

                    // check range
                    if ( $h >= $lower[0] && $h <= $upper[0] &&
                        $s >= $lower[1] && $s <= $upper[1] &&
                        $v >= $lower[2] && $v <= $upper[2]) {
                        continue; // Keep color
                    } else {
                        // Convert to grayscale
                        $gray = intval(($r + $g + $b) / 3);
                        $gray_color = imagecolorallocate($image, $gray, $gray, $gray);
                        imagesetpixel($image, $x, $y, $gray_color);
                    }
                }
            }
        }

        imagejpeg($image, Storage::path($this->filtered_path));
        imagedestroy($image);

    }

    private function rgbToHsv($r, $g, $b) {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        // Hue calculation
        if ($delta == 0) {
            $h = 0;
        } elseif ($max == $r) {
            $h = 60 * fmod((($g - $b) / $delta), 6);
        } elseif ($max == $g) {
            $h = 60 * ((($b - $r) / $delta) + 2);
        } else {
            $h = 60 * ((($r - $g) / $delta) + 4);
        }

        if ($h < 0) $h += 360;

        // Saturation
        $s = ($max == 0) ? 0 : ($delta / $max);

        // Value
        $v = $max;

        return [$h, $s * 100, $v * 100]; // Return H in degrees, S and V in %
    }

}

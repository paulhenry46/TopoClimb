<?php

namespace App\Console\Commands;

use App\Jobs\ImageFilter;
use Illuminate\Console\Command;

class TestImageFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-image-filter';

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
        $color = 'red';
         $colorToInterval = config('climb.colorsInterval')[$color];
         //$image = imagecreatefromjpeg($this->picture);
        $image = imagecreatefromjpeg('/home/paulhenrys/Images/test.jpg');
        $width = imagesx($image);
        $height = imagesy($image);

        if($color == 'red'){
            $upper[0] = $colorToInterval[0][1];
            $upper[1] = $colorToInterval[1][1];
            $lower[0] = $colorToInterval[0][0];
            $lower[1] = $colorToInterval[1][0];

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
            $upper = $colorToInterval[1];
            $lower = $colorToInterval[0];

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

        imagejpeg($image, '/home/paulhenrys/Images/test_result.jpg');
        imagedestroy($image);
         $this->info("OK");
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

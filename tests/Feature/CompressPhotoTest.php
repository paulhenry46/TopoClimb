<?php

use App\Jobs\CompressPhoto;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

test('compresses large photos over 2MB', function () {
    // Create a large test image (over 2MB) with random pixels to prevent compression
    $image = imagecreatetruecolor(3000, 3000);

    // Fill with random colors to make it less compressible
    for ($x = 0; $x < 3000; $x += 10) {
        for ($y = 0; $y < 3000; $y += 10) {
            $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            imagefilledrectangle($image, $x, $y, $x + 10, $y + 10, $color);
        }
    }

    $tempPath = sys_get_temp_dir().'/test_large_image.jpg';
    imagejpeg($image, $tempPath, 100);
    imagedestroy($image);

    // Store it in fake storage
    $path = 'photos/test/route-1';
    Storage::put($path, file_get_contents($tempPath));
    unlink($tempPath);

    $originalSize = Storage::size($path);

    // Ensure the test image is actually over 2MB
    expect($originalSize)->toBeGreaterThan(2 * 1024 * 1024);

    // Run the compression job
    $job = new CompressPhoto($path);
    $job->handle();

    // Verify the file still exists
    expect(Storage::exists($path))->toBeTrue();

    $compressedSize = Storage::size($path);

    // Verify the file was compressed (should be smaller)
    expect($compressedSize)->toBeLessThan($originalSize);

    // Compressed should ideally be close to or under 2MB
    expect($compressedSize)->toBeLessThanOrEqual(2.5 * 1024 * 1024); // Allow some margin
});

test('does not compress photos under 2MB', function () {
    // Create a small test image (under 2MB)
    $image = imagecreatetruecolor(500, 500);
    $tempPath = sys_get_temp_dir().'/test_small_image.jpg';
    imagejpeg($image, $tempPath, 80);
    imagedestroy($image);

    // Store it in fake storage
    $path = 'photos/test/route-2';
    Storage::put($path, file_get_contents($tempPath));
    unlink($tempPath);

    $originalSize = Storage::size($path);

    // Run the compression job
    $job = new CompressPhoto($path);
    $job->handle();

    // Verify the file still exists
    expect(Storage::exists($path))->toBeTrue();

    $finalSize = Storage::size($path);

    // Verify the file was not modified (sizes should be the same)
    expect($finalSize)->toBe($originalSize);
});

test('handles non-existent files gracefully', function () {
    $path = 'photos/test/non-existent.jpg';

    // Run the compression job - should not throw an error
    $job = new CompressPhoto($path);
    $job->handle();

    // Verify file still doesn't exist
    expect(Storage::exists($path))->toBeFalse();
});

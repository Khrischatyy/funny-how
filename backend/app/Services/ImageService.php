<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Intervention\Image\EncodedImage;

class ImageService
{

    public function toJpeg($photo): EncodedImage
    {
        $manager = new ImageManager(Driver::class);
        $image = $manager->read($photo)->toJpeg(90);

        return $image;
    }

    public function saveImageToStorage($image, $path): string
    {
        Storage::disk('s3')->put($path, (string) $image);
        return Storage::disk('s3')->url($path);
    }

    public function optimizeImageInMemory($image): string
    {
        // Save the image temporarily to a variable
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempPath, (string) $image);

        // Optimize the image in memory
        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($tempPath);

        // Get the optimized image content
        $optimizedImage = file_get_contents($tempPath);

        // Clean up the temporary file
        unlink($tempPath);

        return $optimizedImage;
    }
}
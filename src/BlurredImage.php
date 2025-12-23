<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage;

use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class BlurredImage
{
    public function generate(string $existingImagePath): void
    {
        $exitCode = Artisan::call('blurred-image:generate', [
            'path' => $existingImagePath,
        ]);

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Blurred Image exception: Blurrable image generation has failed. STDERR: '.Artisan::output(),
            );
        }
    }
}

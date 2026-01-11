<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage;

use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class BlurredImage
{
    public function generate(string $path, ?bool $processDirectory = null): void
    {
        $arguments = [
            'path' => $path,
        ];

        if ($processDirectory ?? is_dir($path)) {
            $arguments['--directory'] = true;
        }

        $exitCode = Artisan::call('blurred-image:generate', $arguments);

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Blurred Image exception: Blurrable image generation has failed. STDERR: '.Artisan::output(),
            );
        }
    }
}

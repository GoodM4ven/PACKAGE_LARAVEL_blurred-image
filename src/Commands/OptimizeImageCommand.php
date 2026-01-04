<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage\Commands;

use Illuminate\Console\Command;
use Spatie\Image\Image;
use Throwable;

class OptimizeImageCommand extends Command
{
    protected $signature = 'blurred-image:optimize {path : Path of the source image}';

    protected $description = 'Optimize an image in place using Spatie Image.';

    public function handle(): int
    {
        if (! class_exists(Image::class)) {
            $this->error(
                'Please install spatie/image (or spatie/laravel-medialibrary which includes it) to optimize images.',
            );

            return self::FAILURE;
        }

        $path = (string) $this->argument('path');
        if (! is_file($path)) {
            $this->error('Image file does not exist.');

            return self::FAILURE;
        }

        $mime = @mime_content_type($path) ?: '';
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            $this->error('The given file is not an image.');

            return self::FAILURE;
        }

        try {
            Image::load($path)->optimize()->save($path);
        } catch (Throwable $exception) {
            $this->error('Unable to optimize the image: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Image optimized successfully at: [{$path}].");

        return self::SUCCESS;
    }
}

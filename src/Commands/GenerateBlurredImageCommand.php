<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage\Commands;

use Illuminate\Console\Command;
use Spatie\Image\Enums\Constraint;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Image;

class GenerateBlurredImageCommand extends Command
{
    protected $signature = 'blurred-image:generate {path : Path of the source image}';

    protected $description = 'Generate a blurhash-friendly thumbnail for a given image.';

    public function handle(): int
    {
        if (! class_exists(Image::class)) {
            $this->error(
                'Please install spatie/image (or spatie/laravel-medialibrary which includes it) to generate thumbnails.',
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

        $thumbnailWidth = (int) config('blurred-image.thumbnail_resolution.width', 208);
        $thumbnailHeight = (int) config('blurred-image.thumbnail_resolution.height', 117);

        if ($thumbnailWidth < 1 || $thumbnailHeight < 1) {
            $this->error(
                'The thumbnail resolution must be greater than zero. Please update blurred-image.thumbnail_resolution configuration values.',
            );

            return self::FAILURE;
        }

        $image = Image::load($path)->orientation();

        if ($image->getWidth() < $thumbnailWidth || $image->getHeight() < $thumbnailHeight) {
            $this->error('The image is too small.');

            return self::FAILURE;
        }

        $this->comment('Generating thumbnail...');

        $aspectInput = $image->getWidth() / $image->getHeight();
        $aspectTarget = $thumbnailWidth / $thumbnailHeight;

        if ($aspectInput >= $aspectTarget) {
            $image->height($thumbnailHeight, [Constraint::PreserveAspectRatio]);
            $image->crop($thumbnailWidth, $thumbnailHeight, CropPosition::Center);
        } else {
            $image->width($thumbnailWidth, [Constraint::PreserveAspectRatio]);
            $image->crop($thumbnailWidth, $thumbnailHeight, CropPosition::Center);
        }

        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $thumbnailPath = pathinfo($path, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR."{$filename}-thumbnail.{$extension}";

        $image->format(strtolower($extension))->save($thumbnailPath);

        $this->info("Blurred thumbnail generated successfully at: [{$thumbnailPath}].");

        return self::SUCCESS;
    }
}

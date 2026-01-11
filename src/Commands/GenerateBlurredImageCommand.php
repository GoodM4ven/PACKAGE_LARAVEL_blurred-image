<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\Image\Enums\Constraint;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Image;

class GenerateBlurredImageCommand extends Command
{
    protected $signature = 'blurred-image:generate {path : Path of the source image or directory} {--directory : Process all images within the directory recursively}';

    protected $description = 'Generate a blurhash-friendly thumbnail for a given image or directory.';

    public function handle(): int
    {
        if (! class_exists(Image::class)) {
            $this->error(
                'Please install spatie/image (or spatie/laravel-medialibrary which includes it) to generate thumbnails.',
            );

            return self::FAILURE;
        }

        $path = (string) $this->argument('path');
        $processDirectory = (bool) $this->option('directory');

        $thumbnailWidth = (int) config('blurred-image.thumbnail_resolution.width', 208);
        $thumbnailHeight = (int) config('blurred-image.thumbnail_resolution.height', 117);

        if ($thumbnailWidth < 1 || $thumbnailHeight < 1) {
            $this->error(
                'The thumbnail resolution must be greater than zero. Please update blurred-image.thumbnail_resolution configuration values.',
            );

            return self::FAILURE;
        }

        if ($processDirectory) {
            if (! is_dir($path)) {
                $this->error('Directory does not exist.');

                return self::FAILURE;
            }

            $imagePaths = $this->getImagePathsFromDirectory($path);
            if ($imagePaths === []) {
                $this->error('No supported images were found in the directory.');

                return self::FAILURE;
            }

            $hasFailures = false;
            foreach ($imagePaths as $imagePath) {
                if ($this->generateThumbnailForImage($imagePath, $thumbnailWidth, $thumbnailHeight) !== self::SUCCESS) {
                    $hasFailures = true;
                }
            }

            if ($hasFailures) {
                return self::FAILURE;
            }

            $this->info('Blurred thumbnails generated successfully.');

            return self::SUCCESS;
        }

        return $this->generateThumbnailForImage($path, $thumbnailWidth, $thumbnailHeight);
    }

    protected function generateThumbnailForImage(string $path, int $thumbnailWidth, int $thumbnailHeight): int
    {
        if (! is_file($path)) {
            $this->error('Image file does not exist.');

            return self::FAILURE;
        }

        if (! $this->isSupportedImage($path)) {
            $this->error('The given file is not an image.');

            return self::FAILURE;
        }

        $image = Image::load($path)->orientation();

        if ($image->getWidth() < $thumbnailWidth || $image->getHeight() < $thumbnailHeight) {
            $this->error('The image is too small.');

            return self::FAILURE;
        }

        $this->comment('Generating thumbnail...');
        $this->comment("Source image: [{$path}].");

        $aspectInput = $image->getWidth() / $image->getHeight();
        $aspectTarget = $thumbnailWidth / $thumbnailHeight;

        if ($aspectInput >= $aspectTarget) {
            $image->height($thumbnailHeight, [Constraint::PreserveAspectRatio]);
            $image->crop($thumbnailWidth, $thumbnailHeight, CropPosition::Center);
        } else {
            $image->width($thumbnailWidth, [Constraint::PreserveAspectRatio]);
            $image->crop($thumbnailWidth, $thumbnailHeight, CropPosition::Center);
        }

        $thumbnailPath = $this->buildThumbnailPath($path);
        $image->format($this->getImageFormat($path))->save($thumbnailPath);

        if ($this->shouldOptimizeGeneration()) {
            $this->comment('Optimizing original and blurred images...');

            if (! $this->optimizeGeneratedImages($path, $thumbnailPath)) {
                return self::FAILURE;
            }
        }

        $this->info("Blurred thumbnail generated successfully at: [{$thumbnailPath}].");

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function getImagePathsFromDirectory(string $directory): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        $paths = [];
        foreach ($iterator as $fileInfo) {
            if (! $fileInfo->isFile()) {
                continue;
            }

            $path = $fileInfo->getPathname();
            if (! $this->isSupportedImage($path)) {
                continue;
            }

            $paths[] = $path;
        }

        sort($paths);

        return $paths;
    }

    protected function buildThumbnailPath(string $path): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $conversionName = (string) config('blurred-image.conversion_name');

        return pathinfo($path, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR."{$filename}-{$conversionName}.{$extension}";
    }

    protected function getImageFormat(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    protected function isSupportedImage(string $path): bool
    {
        $mime = @mime_content_type($path) ?: '';

        return in_array($mime, $this->supportedImageMimeTypes(), true);
    }

    /**
     * @return array<int, string>
     */
    protected function supportedImageMimeTypes(): array
    {
        return ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    }

    protected function shouldOptimizeGeneration(): bool
    {
        return (bool) config('blurred-image.is_generation_optimized', true);
    }

    protected function optimizeGeneratedImages(string ...$paths): bool
    {
        foreach ($paths as $path) {
            $exitCode = $this->call(OptimizeImageCommand::class, [
                'path' => $path,
            ]);

            if ($exitCode !== self::SUCCESS) {
                return false;
            }
        }

        return true;
    }
}

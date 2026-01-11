<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\Image\Image;
use Throwable;

class OptimizeImageCommand extends Command
{
    protected $signature = 'blurred-image:optimize {path : Path of the source image or directory} {--directory : Process all images within the directory recursively}';

    protected $description = 'Optimize an image or directory in place using Spatie Image.';

    public function handle(): int
    {
        if (! class_exists(Image::class)) {
            $this->error(
                'Please install spatie/image (or spatie/laravel-medialibrary which includes it) to optimize images.',
            );

            return self::FAILURE;
        }

        $path = (string) $this->argument('path');
        $processDirectory = (bool) $this->option('directory');

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
                if ($this->optimizeImage($imagePath) !== self::SUCCESS) {
                    $hasFailures = true;
                }
            }

            if ($hasFailures) {
                return self::FAILURE;
            }

            $this->info('Images optimized successfully.');

            return self::SUCCESS;
        }

        return $this->optimizeImage($path);
    }

    protected function optimizeImage(string $path): int
    {
        if (! is_file($path)) {
            $this->error('Image file does not exist.');

            return self::FAILURE;
        }

        if (! $this->isSupportedImage($path)) {
            $this->error('The given file is not an image.');

            return self::FAILURE;
        }

        try {
            $this->comment("Optimizing image: [{$path}]...");
            Image::load($path)->optimize()->save($path);
        } catch (Throwable $exception) {
            $this->error('Unable to optimize the image: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Image optimized successfully at: [{$path}].");

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
}

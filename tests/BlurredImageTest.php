<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('exposes sensible default configuration', function () {
    expect(config('blurred-image.conversion_name'))->toBe('blur-thumbnail')
        ->and(config('blurred-image.thumbnail_resolution.width'))->toBe(208)
        ->and(config('blurred-image.thumbnail_resolution.height'))->toBe(117);
});

it('generates a blurred thumbnail via the artisan command', function () {
    $sourcePath = storage_path('app/testing/blurred-image-source.jpg');
    $optimizedSourcePath = storage_path('app/testing/blurred-image-source.webp');
    $conversionName = config('blurred-image.conversion_name');
    $thumbnailPath = storage_path("app/testing/blurred-image-source-{$conversionName}.jpg");
    $optimizedThumbnailPath = storage_path("app/testing/blurred-image-source-{$conversionName}.webp");

    if (! is_dir(dirname($sourcePath))) {
        mkdir(dirname($sourcePath), 0755, true);
    }

    copy(public_path('images/asset.jpg'), $sourcePath);
    $originalMtime = time() - 10;
    touch($sourcePath, $originalMtime);

    foreach ([$thumbnailPath, $optimizedSourcePath, $optimizedThumbnailPath] as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }

    try {
        artisan('blurred-image:generate', [
            'path' => $sourcePath,
        ])
            ->expectsOutputToContain('Generating thumbnail...')
            ->expectsOutputToContain('Optimizing original and blurred images...')
            ->expectsOutputToContain("Image optimized successfully at: [{$optimizedSourcePath}].")
            ->expectsOutputToContain("Image optimized successfully at: [{$optimizedThumbnailPath}].")
            ->expectsOutputToContain(
                "Blurred thumbnail generated successfully at: [{$optimizedThumbnailPath}].",
            )
            ->assertExitCode(0);

        expect(is_file($optimizedThumbnailPath))->toBeTrue()
            ->and(filemtime($optimizedSourcePath))->toBeGreaterThan($originalMtime);
    } finally {
        foreach ([$sourcePath, $optimizedSourcePath, $thumbnailPath, $optimizedThumbnailPath] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
});

it('skips optimization when the generation flag is disabled', function () {
    $sourcePath = storage_path('app/testing/blurred-image-unoptimized-source.jpg');
    $conversionName = config('blurred-image.conversion_name');
    $thumbnailPath = storage_path("app/testing/blurred-image-unoptimized-source-{$conversionName}.jpg");

    if (! is_dir(dirname($sourcePath))) {
        mkdir(dirname($sourcePath), 0755, true);
    }

    copy(public_path('images/asset.jpg'), $sourcePath);
    $originalMtime = time() - 20;
    touch($sourcePath, $originalMtime);

    $optimizedSourcePath = storage_path('app/testing/blurred-image-unoptimized-source.webp');
    $optimizedThumbnailPath = storage_path("app/testing/blurred-image-unoptimized-source-{$conversionName}.webp");

    foreach ([$thumbnailPath, $optimizedSourcePath, $optimizedThumbnailPath] as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }

    config()->set('blurred-image.is_generation_optimized', false);

    try {
        artisan('blurred-image:generate', [
            'path' => $sourcePath,
        ])
            ->doesntExpectOutputToContain('Optimizing original and blurred images...')
            ->doesntExpectOutputToContain('Image optimized successfully at:')
            ->assertExitCode(0);

        expect(is_file($thumbnailPath))->toBeTrue()
            ->and(filemtime($sourcePath))->toBe($originalMtime);
    } finally {
        foreach ([$sourcePath, $optimizedSourcePath, $thumbnailPath, $optimizedThumbnailPath] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
});

it('optimizes an image via the artisan command', function () {
    $sourcePath = storage_path('app/testing/blurred-image-optimize-source.jpg');
    $optimizedPath = storage_path('app/testing/blurred-image-optimize-source.webp');

    if (! is_dir(dirname($sourcePath))) {
        mkdir(dirname($sourcePath), 0755, true);
    }

    copy(public_path('images/asset.jpg'), $sourcePath);
    $originalMtime = time() - 30;
    touch($sourcePath, $originalMtime);

    if (is_file($optimizedPath)) {
        unlink($optimizedPath);
    }

    try {
        artisan('blurred-image:optimize', [
            'path' => $sourcePath,
        ])
            ->expectsOutputToContain("Image optimized successfully at: [{$optimizedPath}].")
            ->assertExitCode(0);

        expect(is_file($sourcePath))->toBeFalse()
            ->and(is_file($optimizedPath))->toBeTrue()
            ->and(filemtime($optimizedPath))->toBeGreaterThan($originalMtime);
    } finally {
        foreach ([$sourcePath, $optimizedPath] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
});

it('fails when the optimize command receives a missing file', function () {
    $missingPath = storage_path('app/testing/blurred-image-missing-optimize.jpg');

    artisan('blurred-image:optimize', [
        'path' => $missingPath,
    ])
        ->expectsOutput('Image file does not exist.')
        ->assertExitCode(1);
});

it('fails when the artisan command receives a missing file', function () {
    $missingPath = storage_path('app/testing/blurred-image-missing.jpg');

    artisan('blurred-image:generate', [
        'path' => $missingPath,
    ])
        ->expectsOutput('Image file does not exist.')
        ->assertExitCode(1);
});

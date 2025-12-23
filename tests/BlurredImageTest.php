<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('exposes sensible default configuration', function () {
    expect(config('blurred-image.conversion_name'))->toBe('blurred-thumbnail')
        ->and(config('blurred-image.thumbnail_resolution.width'))->toBe(208)
        ->and(config('blurred-image.thumbnail_resolution.height'))->toBe(117);
});

it('generates a blurred thumbnail via the artisan command', function () {
    $sourcePath = storage_path('app/testing/blurred-image-source.jpg');
    $thumbnailPath = storage_path('app/testing/blurred-image-source-thumbnail.jpg');

    if (! is_dir(dirname($sourcePath))) {
        mkdir(dirname($sourcePath), 0755, true);
    }

    copy(public_path('images/asset.jpg'), $sourcePath);

    if (is_file($thumbnailPath)) {
        unlink($thumbnailPath);
    }

    try {
        artisan('blurred-image:generate', [
            'path' => $sourcePath,
        ])
            ->expectsOutputToContain('Generating thumbnail...')
            ->expectsOutputToContain('Blurred thumbnail generated successfully')
            ->assertExitCode(0);

        expect(is_file($thumbnailPath))->toBeTrue();
    } finally {
        if (is_file($sourcePath)) {
            unlink($sourcePath);
        }

        if (is_file($thumbnailPath)) {
            unlink($thumbnailPath);
        }
    }
});

it('fails when the artisan command receives a missing file', function () {
    $missingPath = storage_path('app/testing/blurred-image-missing.jpg');

    artisan('blurred-image:generate', [
        'path' => $missingPath,
    ])
        ->expectsOutput('Image file does not exist.')
        ->assertExitCode(1);
});

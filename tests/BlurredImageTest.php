<?php

declare(strict_types=1);

it('exposes sensible default configuration', function () {
    expect(config('blurred-image.conversion_name'))->toBe('blurred-thumbnail')
        ->and(config('blurred-image.thumbnail_resolution.width'))->toBe(208)
        ->and(config('blurred-image.thumbnail_resolution.height'))->toBe(117);
});

// TODO test the Artisan command

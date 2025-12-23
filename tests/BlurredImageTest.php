<?php

declare(strict_types=1);

use GoodMaven\BlurredImage\Facades\BlurredImage;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

it('exposes sensible default configuration', function () {
    expect(config('blurred-image.conversion_name'))->toBe('blurred-thumbnail')
        ->and(config('blurred-image.thumbnail_resolution.width'))->toBe(208)
        ->and(config('blurred-image.thumbnail_resolution.height'))->toBe(117);
});

it('runs the generator command via the facade', function () {
    $kernel = new class implements Kernel
    {
        public array $calls = [];

        public function bootstrap(): void {}

        public function handle($input, $output = null): int
        {
            return 0;
        }

        public function call($command, array $parameters = [], $outputBuffer = null): int
        {
            $this->calls[] = [$command, $parameters];

            return 0;
        }

        public function queue($command, array $parameters = [])
        {
            return 0;
        }

        public function all(): array
        {
            return [];
        }

        public function output(): string
        {
            return '';
        }

        public function terminate($input, $status): void {}
    };

    Artisan::swap($kernel);

    BlurredImage::generate('storage/app/example.jpg');

    expect($kernel->calls)->toContain([
        'blurred-image:generate',
        ['path' => 'storage/app/example.jpg'],
    ]);
});

it('renders the blurred image component with placeholders when no media is present', function () {
    $html = (string) view('blurred-image::components.blurred-image')->render();

    expect($html)->toContain('empty-media-placeholder-thumb.png')
        ->and($html)->toContain('blurred-image.js');
});

<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('generates a blurred thumbnail via the artisan command', function () {
    $source = dirname(__DIR__).'/resources/dist/empty-media-placeholder.png';
    $workdir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'blurred-image-tests';
    $target = $workdir.DIRECTORY_SEPARATOR.'source.png';
    $thumbnail = $workdir.DIRECTORY_SEPARATOR.'source-thumbnail.png';

    if (! is_dir($workdir)) {
        mkdir($workdir, 0o775, true);
    }

    copy($source, $target);

    expect(Artisan::call('blurred-image:generate', ['path' => $target]))->toBe(0);
    expect(is_file($thumbnail))->toBeTrue();

    @unlink($target);
    @unlink($thumbnail);
});

it('renders the component without media library using explicit paths', function () {
    $dataUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAOb6ZeoAAAAASUVORK5CYII=';

    $html = (string) view('blurred-image::components.blurred-image', [
        'imagePath' => $dataUri,
        'thumbnailImagePath' => $dataUri,
        'isDisplayEnforced' => true,
    ])->render();

    $escapedDataUri = trim(json_encode($dataUri), '"');

    expect($html)->toContain($escapedDataUri)
        ->and($html)->toContain('blurred-image.js');
});

it('renders the component for a media library model', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $media = $user->addMedia(dirname(__DIR__).'/resources/dist/empty-media-placeholder.png')
        ->preservingOriginal()
        ->toMediaCollection('profile');

    $thumbnailUrl = $user->getFirstBlurredImageThumbnailUrl('profile');
    $conversionUrl = $media->getUrl(config('blurred-image.conversion_name'));

    expect($media->hasGeneratedConversion(config('blurred-image.conversion_name')))->toBeTrue();

    $html = (string) view('blurred-image::components.blurred-image', [
        'model' => $user->fresh(),
        'collection' => 'profile',
        'mediaIndex' => 0,
        'conversion' => config('blurred-image.conversion_name'),
        'isDisplayEnforced' => true,
    ])->render();

    $escapedThumbnailUrl = str_replace('/', '\/', $thumbnailUrl);
    $escapedConversionUrl = str_replace('/', '\/', $conversionUrl);

    expect($html)->toContain($escapedThumbnailUrl)
        ->and($html)->toContain($escapedConversionUrl);
});

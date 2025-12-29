<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

function registerBlurredImageRoute(string $componentMarkup, array $data = []): string
{
    $path = '/browser/blurred-image-'.Str::uuid()->toString();

    Route::get($path, function () use ($componentMarkup, $data) {
        $body = Blade::render($componentMarkup, $data);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Blurred Image Browser Tests</title>
    </head>
    <body>
        {$body}
    </body>
</html>
HTML;
    });

    return $path;
}

function createMediaLibraryUser(): User
{
    $user = User::factory()->create();

    $user
        ->addMedia(public_path('images/profile.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('profile');

    return $user->refresh();
}

it('uses placeholder markup when no links are provided', function () {
    $path = registerBlurredImageRoute(
        <<<'BLADE'
<section data-testid="blurred-image">
    <x-goodmaven::blurred-image
        :image-path="$imagePath"
        :thumbnail-image-path="$thumbnailPath"
        width-class="w-full"
        height-class="h-64"
        :is-display-enforced="true"
        :is-eager-loaded="true"
    />
</section>
BLADE,
        [
            'imagePath' => null,
            'thumbnailPath' => null,
        ],
    );

    visit($path)
        ->assertSourceHas('empty-media-placeholder.png')
        ->assertSourceHas('empty-media-placeholder-thumb.png');
});

it('includes fallback details when the image links are broken', function () {
    $path = registerBlurredImageRoute(
        <<<'BLADE'
<section data-testid="blurred-image">
    <x-goodmaven::blurred-image
        :image-path="$imagePath"
        :thumbnail-image-path="$thumbnailPath"
        width-class="w-full"
        height-class="h-64"
        :is-display-enforced="true"
        :is-eager-loaded="true"
    />
</section>
BLADE,
        [
            'imagePath' => asset('images/missing.jpg'),
            'thumbnailPath' => asset('images/missing-blur-thumbnail.jpg'),
        ],
    );

    visit($path)
        ->assertSourceHas('empty-media-placeholder.png')
        ->assertSourceHas('missing.jpg');
});

it('renders direct file paths in the markup', function () {
    $path = registerBlurredImageRoute(
        <<<'BLADE'
<section data-testid="blurred-image">
    <x-goodmaven::blurred-image
        :image-path="$imagePath"
        :thumbnail-image-path="$thumbnailPath"
        width-class="w-full"
        height-class="h-64"
        :is-display-enforced="true"
        :is-eager-loaded="true"
    />
</section>
BLADE,
        [
            'imagePath' => asset('images/asset.jpg'),
            'thumbnailPath' => asset('images/asset-blur-thumbnail.jpg'),
        ],
    );

    visit($path)
        ->assertSourceHas('asset.jpg')
        ->assertSourceHas('asset-blur-thumbnail.jpg');
});

it('infers the thumbnail path from the image path using the configured conversion name', function () {
    $path = registerBlurredImageRoute(
        <<<'BLADE'
<section data-testid="blurred-image">
    <x-goodmaven::blurred-image
        :image-path="$imagePath"
        width-class="w-full"
        height-class="h-64"
        :is-display-enforced="true"
        :is-eager-loaded="true"
    />
</section>
BLADE,
        [
            'imagePath' => asset('images/asset.jpg'),
        ],
    );

    visit($path)
        ->assertSourceHas('asset.jpg')
        ->assertSourceHas('asset-'.config('blurred-image.conversion_name').'.jpg');
});

it('renders media library URLs in the markup', function () {
    $user = createMediaLibraryUser();
    $media = $user->getFirstMedia('profile');
    $fullUrl = $media?->getUrl('');
    $blurredUrl = $media?->getUrl(config('blurred-image.conversion_name'));

    $path = registerBlurredImageRoute(
        <<<'BLADE'
<section data-testid="blurred-image">
    <x-goodmaven::blurred-image
        :model="$user"
        collection="profile"
        width-class="w-full"
        height-class="h-64"
        :is-display-enforced="true"
        :is-eager-loaded="true"
    />
</section>
BLADE,
        [
            'user' => $user,
        ],
    );

    $page = visit($path);

    if (is_string($fullUrl)) {
        $page->assertSourceHas(basename($fullUrl));
    }

    if (is_string($blurredUrl)) {
        $page->assertSourceHas('blur-thumbnail');
    }
});

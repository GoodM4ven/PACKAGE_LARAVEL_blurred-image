<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;

Route::get('/', function () {
    $imagePath = '/images/asset.jpg';
    $thumbnailPath = '/images/asset-blurred-thumbnail.jpg';
    $profileImagePath = '/images/profile.jpg';
    $profileThumbnailPath = '/images/profile-thumbnail.jpg';
    $innerContentImagePath = '/images/inner-content.jpg';
    $innerContentThumbnailPath = '/images/inner-content-thumbnail.jpg';
    $intersectedFullyImagePath = '/images/intersected-fully.jpg';
    $intersectedFullyThumbnailPath = '/images/intersected-fully-thumbnail.jpg';
    $delayedImagePath = '/images/delayed.jpg';
    $delayedThumbnailPath = '/images/delayed-thumbnail.jpg';

    // @phpstan-ignore-next-line
    $demoUser = User::query()->firstOrCreate(
        ['email' => 'blurred@example.com'],
        [
            'name' => 'Blurred Avatar',
            'password' => 'password',
        ],
    );

    // @phpstan-ignore-next-line
    $profileMedia = $demoUser->getFirstMedia('profile');

    if (! $profileMedia || $profileMedia->file_name !== basename($profileImagePath)) {
        // @phpstan-ignore-next-line
        $demoUser->clearMediaCollection('profile');

        // @phpstan-ignore-next-line
        $demoUser
            ->addMedia(public_path(ltrim($profileImagePath, '/')))
            ->preservingOriginal()
            ->toMediaCollection('profile');
    }

    // @phpstan-ignore-next-line
    $demoUser->refresh();

    return view('demo', [
        'imagePath' => $imagePath,
        'thumbnailPath' => $thumbnailPath,
        'demoUser' => $demoUser,
        'conversionName' => config('blurred-image.conversion_name'),
        'profileImagePath' => $profileImagePath,
        'profileThumbnailPath' => $profileThumbnailPath,
        'innerContentImagePath' => $innerContentImagePath,
        'innerContentThumbnailPath' => $innerContentThumbnailPath,
        'intersectedFullyImagePath' => $intersectedFullyImagePath,
        'intersectedFullyThumbnailPath' => $intersectedFullyThumbnailPath,
        'delayedImagePath' => $delayedImagePath,
        'delayedThumbnailPath' => $delayedThumbnailPath,
    ]);
});

if (app()->environment('testing')) {
    Route::get('/browser/workbench-demo', function () {
        $imageUrl = asset('vendor/blurred-image/empty-media-placeholder.png');

        $html = Blade::render(<<<'BLADE'
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Blurred Image Demo</title>
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="antialiased bg-slate-950 text-slate-100">
        <main class="mx-auto max-w-4xl space-y-10 px-5 py-10">
            <section>
                <h2 class="text-lg font-semibold mb-4">Explicit paths</h2>
                <div data-testid="explicit-blur">
                    <x-goodmaven::blurred-image
                        :image-path="$imageUrl"
                        :thumbnail-image-path="$imageUrl"
                        width-class="w-full max-w-xl"
                        height-class="h-64"
                        :is-display-enforced="true"
                        class="rounded-xl"
                    />
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold mb-4">Avatar</h2>
                <div data-testid="avatar-blur">
                    <x-goodmaven::blurred-image
                        :image-path="$imageUrl"
                        :thumbnail-image-path="$imageUrl"
                        width-class="w-48"
                        height-class="h-48"
                        :is-display-enforced="true"
                        :is-eager-loaded="true"
                    />
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold mb-4">Slot overlay</h2>
                <div data-testid="inner-content-blur">
                    <x-goodmaven::blurred-image
                        :image-path="$imageUrl"
                        :thumbnail-image-path="$imageUrl"
                        width-class="w-full"
                        height-class="h-80"
                        :is-display-enforced="true"
                    >
                        <p class="text-2xl font-semibold text-white">Overlayed itinerary</p>
                    </x-goodmaven::blurred-image>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold mb-4">Fully intersected</h2>
                <div data-testid="fully-intersected-blur">
                    <x-goodmaven::blurred-image
                        :image-path="$imageUrl"
                        :thumbnail-image-path="$imageUrl"
                        width-class="w-full"
                        height-class="h-80"
                        :is-eager-loaded="true"
                        :is-display-enforced="false"
                    />
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold mb-4">Delayed download</h2>
                <div data-testid="delayed-download-blur">
                    <x-goodmaven::blurred-image
                        :image-path="$imageUrl"
                        :thumbnail-image-path="$imageUrl"
                        width-class="w-full"
                        height-class="h-64"
                        :is-eager-loaded="false"
                        :is-display-enforced="false"
                    />
                </div>
            </section>
        </main>
    </body>
</html>
BLADE, [
            'imageUrl' => $imageUrl,
        ]);

        return $html;
    });
}

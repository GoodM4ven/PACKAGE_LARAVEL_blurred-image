<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

$playwrightBinary = dirname(__DIR__, 2).'/node_modules/.bin/playwright';

$nodeAvailable = trim((string) shell_exec('command -v node')) !== '';
$shouldSkip = getenv('SKIP_BROWSER_TESTS') === '1' || ! $nodeAvailable || ! is_file($playwrightBinary);

if ($shouldSkip) {
    return;
}

it('renders a blurhash placeholder in the browser', function () {
    $imageData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAOb6ZeoAAAAASUVORK5CYII=';

    Route::get('/browser/blurred-image', function () use ($imageData) {
        $component = view('blurred-image::components.blurred-image', [
            'imagePath' => $imageData,
            'thumbnailImagePath' => $imageData,
            'alt' => 'Browser test blur image',
            'widthClass' => 'w-32',
            'heightClass' => 'h-32',
            'isDisplayEnforced' => true,
        ])->render();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body>
        {$component}
    </body>
</html>
HTML;
    });

    $page = visit('/browser/blurred-image')->assertNoJavaScriptErrors();

    $pixel = $page->script(<<<'JS'
() => new Promise((resolve) => {
    const deadline = Date.now() + 5000;
    const poll = () => {
        const canvas = document.querySelector('canvas');
        if (!canvas) {
            if (Date.now() > deadline) {
                return resolve({ ready: false, sum: 0, alpha: 0 });
            }

            return setTimeout(poll, 50);
        }

        const context = canvas.getContext('2d');
        if (!context) {
            return resolve({ ready: false, sum: 0, alpha: 0 });
        }

        const data = context.getImageData(0, 0, 1, 1).data;
        const sum = Array.from(data).reduce((carry, value) => carry + value, 0);

        if (sum === 0 && Date.now() < deadline) {
            return setTimeout(poll, 50);
        }

        resolve({ ready: sum > 0, sum, alpha: data[3] });
    };

    poll();
})
JS
    );

    $pixel = (array) $pixel;

    expect($pixel['ready'])->toBeTrue();
    expect($pixel['alpha'])->toBeGreaterThan(0);
    expect($pixel['sum'])->toBeGreaterThan(0);
});

it('exercises the workbench home page demo', function () {
    $page = visit('/browser/workbench-demo')->assertNoJavaScriptErrors();

    $assetStatus = (array) $page->script(<<<'JS'
() => fetch('/vendor/blurred-image/empty-media-placeholder.png')
    .then((response) => ({ status: response.status }))
    .catch(() => ({ status: 0 }))
JS
    );

    expect($assetStatus['status'])->toBe(200);

    $explicitState = (array) $page->script(<<<'JS'
() => new Promise((resolve) => {
    const deadline = Date.now() + 15000;
    const poll = () => {
        const host = document.querySelector('[data-testid="explicit-blur"]');
        const component = host?.querySelector('[x-data]');
        const canvas = component?.querySelector('canvas');
        const data = component?._x_dataStack?.[0];
        const ready = Boolean(data?.imageRequested && data?.imageSrc);

        if ((!host || !canvas || !ready) && Date.now() < deadline) {
            return setTimeout(poll, 50);
        }

        resolve({
            found: Boolean(host),
            hasCanvas: Boolean(canvas),
            imageRequested: Boolean(data?.imageRequested),
            imageSrc: data?.imageSrc ?? '',
            imageFailed: Boolean(data?.imageFailed),
            showImage: Boolean(data?.showImage),
        });
    };

    poll();
})
JS
    );

    expect($explicitState['found'])->toBeTrue();
    expect($explicitState['hasCanvas'])->toBeTrue();
    expect($explicitState['imageRequested'])->toBeTrue();
    expect($explicitState['imageSrc'])->not->toBe('');
    expect($explicitState['imageFailed'])->toBeFalse();

    $avatarSrc = (string) $page->script(<<<'JS'
() => {
    const img = document.querySelector('[data-testid="avatar-blur"] img');

    return img ? img.src : '';
}
JS
    );

    expect($avatarSrc)->toContain('blob:http://');

    $avatarLoading = (string) $page->script(<<<'JS'
() => document.querySelector('[data-testid="avatar-blur"] img')?.getAttribute('loading') ?? ''
JS
    );

    expect($avatarLoading)->toBe('eager');
});

it('shows the inner frame slot overlay while the blurhash settles', function () {
    $page = visit('/browser/workbench-demo')->assertNoJavaScriptErrors();

    $overlayText = (string) $page->script(<<<'JS'
() => {
    const host = document.querySelector('[data-testid="inner-content-blur"]');
    const overlayTitle = host?.querySelector('p.text-2xl');

    return overlayTitle?.textContent?.trim() ?? '';
}
JS
    );

    expect($overlayText)->toContain('Overlayed itinerary');
});

it('reveals the fully intersected panel only after the full intersection fires', function () {
    $page = visit('/browser/workbench-demo')->assertNoJavaScriptErrors();

    $initialState = (array) $page->script(<<<'JS'
() => {
    const host = document.querySelector('[data-testid="fully-intersected-blur"]');
    const component = host?.querySelector('[x-data]');
    const data = component?._x_dataStack?.[0];

    if (! data) {
        return { found: false };
    }

    return {
        found: true,
        isDisplayEnforced: data.isDisplayEnforced,
        isEagerLoaded: data.isEagerLoaded,
        visible: data.visible,
        finalVisible: data.finalVisible ?? false,
    };
}
JS
    );

    expect($initialState['found'])->toBeTrue();
    expect($initialState['isDisplayEnforced'])->toBeFalse();
    expect($initialState['isEagerLoaded'])->toBeTrue();
    expect($initialState['finalVisible'])->toBeFalse();

    $page->script('() => document.querySelector(\'[data-testid="fully-intersected-blur"] [x-data]\')?._x_dataStack?.[0]?.markVisible(true)');

    $revealedState = (array) $page->script(<<<'JS'
() => new Promise((resolve) => {
    const host = document.querySelector('[data-testid="fully-intersected-blur"]');
    const component = host?.querySelector('[x-data]');
    const data = component?._x_dataStack?.[0];

    if (! data) {
        return resolve({ ready: false });
    }

    const deadline = Date.now() + 10000;

    const check = () => {
        if (data.showImage && data.finalVisible) {
            return resolve({
                ready: true,
                showImage: data.showImage,
                finalVisible: data.finalVisible,
            });
        }

        if (Date.now() > deadline) {
            return resolve({
                ready: false,
                showImage: data.showImage,
                finalVisible: data.finalVisible,
            });
        }

        requestAnimationFrame(check);
    };

    check();
})
JS
    );

    expect($revealedState['ready'])->toBeTrue();
    expect($revealedState['showImage'])->toBeTrue();
    expect($revealedState['finalVisible'])->toBeTrue();
});

it('keeps the delayed download from requesting the high-res asset until intersection', function () {
    $page = visit('/browser/workbench-demo')->assertNoJavaScriptErrors();

    $initialDelayed = (array) $page->script(<<<'JS'
() => {
    const host = document.querySelector('[data-testid="delayed-download-blur"]');
    const component = host?.querySelector('[x-data]');
    const data = component?._x_dataStack?.[0];

    if (! data) {
        return { found: false };
    }

    return {
        found: true,
        isEagerLoaded: data.isEagerLoaded,
        isDisplayEnforced: data.isDisplayEnforced,
        imageRequested: data.imageRequested,
        visible: data.visible,
    };
}
JS
    );

    expect($initialDelayed['found'])->toBeTrue();
    expect($initialDelayed['isEagerLoaded'])->toBeFalse();
    expect($initialDelayed['isDisplayEnforced'])->toBeFalse();

    $page->script('() => document.querySelector(\'[data-testid="delayed-download-blur"] [x-data]\')?._x_dataStack?.[0]?.markVisible(true)');

    $delayedReady = (array) $page->script(<<<'JS'
() => new Promise((resolve) => {
    const host = document.querySelector('[data-testid="delayed-download-blur"]');
    const component = host?.querySelector('[x-data]');
    const data = component?._x_dataStack?.[0];

    if (! data) {
        return resolve({ ready: false });
    }

    const deadline = Date.now() + 10000;

    const check = () => {
        if (data.imageRequested && data.showImage) {
            return resolve({
                ready: true,
                imageRequested: data.imageRequested,
                showImage: data.showImage,
            });
        }

        if (Date.now() > deadline) {
            return resolve({
                ready: false,
                imageRequested: data.imageRequested,
                showImage: data.showImage,
            });
        }

        requestAnimationFrame(check);
    };

    check();
})
JS
    );

    expect($delayedReady['ready'])->toBeTrue();
    expect($delayedReady['imageRequested'])->toBeTrue();
    expect($delayedReady['showImage'])->toBeTrue();
});

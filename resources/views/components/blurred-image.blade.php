@props([
    'alt' => '',
    'widthClass' => 'w-full',
    'heightClass' => 'h-full',
    'imageClasses' => null,
    'containerClasses' => '',
    'isObjectCentered' => true,
    'isEagerLoaded' => config('blurred-image.is_eager_loaded'),
    'isDisplayEnforced' => config('blurred-image.is_display_enforced'),
    'conversion' => '',
    'model' => null,
    'mediaIndex' => 0,
    'collection' => 'default',
    'media' => null,
    'imagePath' => null,
    'thumbnailImagePath' => null,
])

@once
    @php
        if (!config('blurred-image.conversion_name') || is_null($isEagerLoaded) || is_null($isDisplayEnforced)) {
            throw new \Exception(
                'Blurred Image exception: `conversion_name` is not found in the "blurred-image.php" config file.',
            );
        }
    @endphp

    <link
        href="{{ asset('vendor/blurred-image/blurred-image.css') }}"
        rel="stylesheet"
    >
    <script src="{{ asset('vendor/blurred-image/blurred-image.js') }}"></script>
@endonce

@php
    $placeholder = asset('vendor/blurred-image/empty-media-placeholder.png');
    $placeholderThumb = asset('vendor/blurred-image/empty-media-placeholder-thumb.png');
    $conversionName = config('blurred-image.conversion_name');

    $selectedMedia = $media ?: ($model ? $model->getMedia($collection)->slice($mediaIndex, 1)->first() : null);
    $hasConversion = $conversion && $selectedMedia?->hasGeneratedConversion($conversion);
    $thumbnailLink = $selectedMedia?->getUrl($hasConversion ? $conversion : $conversionName) ?: $thumbnailImagePath;
    $finalMediaLink = $selectedMedia ? $selectedMedia->getUrl('') : $imagePath;

    if (!$thumbnailLink && is_string($finalMediaLink) && $finalMediaLink !== '') {
        $thumbnailLink = (static function (string $path, string $conversionName): ?string {
            $pattern = '/^(.*?)(\\.[^.\\/?#]+)((?:\\?.*)?(?:#.*)?)$/';

            if (!preg_match($pattern, $path, $matches)) {
                return null;
            }

            return "{$matches[1]}-{$conversionName}{$matches[2]}" . ($matches[3] ?? '');
        })($finalMediaLink, $conversionName);
    }

    if (!$thumbnailLink && !$finalMediaLink) {
        if (config('blurred-image.throws_exception')) {
            throw new \Exception('Blurred Image exception: Image not found.');
        }

        $thumbnailLink = $placeholderThumb;
        $finalMediaLink = $placeholder;
    } else {
        $thumbnailLink ??= $placeholderThumb;
        $finalMediaLink ??= $placeholder;
    }

    $rootClass = twMerge($widthClass, $heightClass, 'mx-auto', $attributes->get('class'));
    $containerClass = twMerge(
        $widthClass,
        $heightClass,
        'relative 2xl:mx-auto overflow-hidden bg-gray-500 image-classes',
        $containerClasses,
        $imageClasses,
    );
    $canvasClass = twMerge('absolute inset-0 h-full w-full scale-110 object-cover image-classes z-20', $imageClasses);
    $imageClass = twMerge(
        'absolute inset-0 h-full w-full object-cover image-classes z-30',
        $isObjectCentered ? 'object-center!' : null,
        $imageClasses,
    );
    $rootAttributes = $attributes->except('class');
@endphp

<div {{ $rootAttributes->merge(['class' => $rootClass]) }}>
    <div
        class="{{ $containerClass }}"
        @if ($attributes->has('wire:ignore.self')) wire:ignore.self @endif
        x-data="blurredImage({
            thumbnailLink: @js($thumbnailLink),
            link: @js($finalMediaLink),
            element: $el,
            fallbackLink: @js($placeholder),
            isEagerLoaded: @js($isEagerLoaded),
            isDisplayEnforced: @js($isDisplayEnforced),
        });"
        x-intersect:enter="typeof handlePartialEnter === 'function' ? handlePartialEnter() : markVisible(true)"
        x-intersect:enter.full="markVisible(true)"
        x-intersect:leave.full="markVisible(false)"
    >
        <div
            class="absolute inset-0 z-10 bg-gray-500 transition-opacity duration-300"
            x-bind:class="{ 'opacity-0': !showGray }"
        ></div>

        <canvas
            class="{{ $canvasClass }}"
            x-cloak
            x-show="showBlurhash"
            x-transition:enter="transition-opacity duration-500 ease-out"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-500 ease-in"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            width="32"
            height="32"
        ></canvas>

        <img
            class="{{ $imageClass }}"
            alt="{{ $alt }}"
            x-bind:src="imageSrc"
            loading="{{ $isEagerLoaded ? 'eager' : 'lazy' }}"
            x-init="() => { imgLoaded = $el.complete && $el.naturalHeight !== 0; if (imgLoaded) { markLoaded(); } }"
            x-bind:loading="visible ? 'eager' : 'lazy'"
            x-on:load="markLoaded()"
            x-on:error="handleImageError($event)"
            x-show="showImage"
            x-cloak
            x-transition:enter="transition-opacity duration-500 ease-out"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-400 ease-in"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >

        @if (isset($slot) && $slot->isNotEmpty())
            <div class="relative z-40">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>

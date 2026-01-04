<?php

declare(strict_types=1);

return [

    'thumbnail_resolution' => [
        'width' => 208,
        'height' => 117,
    ],

    /*
     |--------------------------------------------------------------------------
     | Thumbnail Conversion Name (Laravel Media Library)
     |--------------------------------------------------------------------------
     |
     | The conversion name for the Blurhash thumbnail that will be generated.
     |
     | Warning: This shouldn't be used as a conversion name again on the model.
     |
    */

    'conversion_name' => 'blur-thumbnail',

    /*
     |--------------------------------------------------------------------------
     | Is Eager Loaded
     |--------------------------------------------------------------------------
     |
     | Determine whether images shuold begin loading even before they're
     | intersected with (fully) in the view window.
     |
     | Check Alpine.js Intersect plugin: https://alpinejs.dev/plugins/intersect
     |
     */

    'is_eager_loaded' => false,

    /*
     |--------------------------------------------------------------------------
     | Is Display Enforced
     |--------------------------------------------------------------------------
     |
     | Decide whether images should fade in even if they're not intersected with
     | (fully) in the view window.
     |
     | Check Alpine.js Intersect plugin: https://alpinejs.dev/plugins/intersect
     |
     */

    'is_display_enforced' => false,

    /*
     |--------------------------------------------------------------------------
     | Throwing Not Found Exceptions
     |--------------------------------------------------------------------------
     |
     | Should the package throw an exception when a targeted image isn't found?
     | If false, then the empty image placeholder will be displayed instead.
     |
     */

    'throws_exception' => false,

    /*
     |--------------------------------------------------------------------------
     | Optimized Image Generation
     |--------------------------------------------------------------------------
     |
     | Should we run Spatie's image opitimization process on both the blurred-image,
     | and the original image upon using our generation command?
     |
     */

    'is_generation_optimized' => true,

];

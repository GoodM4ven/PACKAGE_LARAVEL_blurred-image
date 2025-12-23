<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \GoodMaven\BlurredImage\BlurredImage
 */
class BlurredImage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \GoodMaven\BlurredImage\BlurredImage::class;
    }
}

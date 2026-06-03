<?php

declare(strict_types=1);

use GoodMaven\BlurredImage\BlurredImageServiceProvider;
use GoodMaven\TailwindMerge\TailwindMergeServiceProvider;
use Laravel\Boost\BoostServiceProvider;
use Livewire\LivewireServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Workbench\App\Providers\TestableWorkbenchServiceProvider;

return [
    BlurredImageServiceProvider::class,
    TailwindMergeServiceProvider::class,
    TestableWorkbenchServiceProvider::class,
    BoostServiceProvider::class,
    LivewireServiceProvider::class,
    MediaLibraryServiceProvider::class,
];

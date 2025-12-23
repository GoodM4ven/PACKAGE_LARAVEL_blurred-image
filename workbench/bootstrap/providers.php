<?php

declare(strict_types=1);

return [
    \GoodMaven\BlurredImage\BlurredImageServiceProvider::class,
    \GoodMaven\TailwindMerge\TailwindMergeServiceProvider::class,
    \Workbench\App\Providers\TestableWorkbenchServiceProvider::class,
    // ? Packages during tests
    \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
    // \Livewire\LivewireServiceProvider::class,
];

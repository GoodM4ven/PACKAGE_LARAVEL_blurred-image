<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage;

use GoodMaven\Anvil\Fixes\RegisterLaravelBoosterJsonSchemaFix;
use GoodMaven\BlurredImage\Commands\GenerateBlurredImageCommand;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BlurredImageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('blurred-image')
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasViewComponents('goodmaven', 'blurred-image')
            ->hasCommand(GenerateBlurredImageCommand::class);
    }

    public function packageRegistered(): void
    {
        RegisterLaravelBoosterJsonSchemaFix::activate();

        $this->app->singleton(BlurredImage::class, fn (): BlurredImage => new BlurredImage);
    }

    public function packageBooted(): void
    {
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'goodmaven');
    }
}

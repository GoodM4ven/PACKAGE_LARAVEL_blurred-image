<?php

declare(strict_types=1);

namespace VendorName\Skeleton;

use GoodMaven\Anvil\Fixes\RegisterLaravelBoosterJsonSchemaFix;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VendorName\Skeleton\Commands\SkeletonCommand;

class SkeletonServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('skeleton')
            ->hasConfigFile()
            ->hasMigration('create_migration_table_name_table')
            ->hasAssets()
            ->hasViews()
            ->hasViewComponents(':vendor_slug', ':package_name')
            ->hasCommand(SkeletonCommand::class);
    }

    public function packageRegistered(): void
    {
        RegisterLaravelBoosterJsonSchemaFix::activate();

        $this->app->singleton(Skeleton::class, fn(): Skeleton => new Skeleton);
    }

    public function packageBooted(): void
    {
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components', ':vendor_slug');
    }
}

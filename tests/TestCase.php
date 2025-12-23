<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage\Tests;

use GoodMaven\Anvil\Concerns\TestableWorkbench;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use TestableWorkbench;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getEnvironmentSetUp($app): void
    {
        $this->setDatabaseTestingEssentials();

        $app['config']->set('media-library', array_merge(
            require dirname(__DIR__).'/vendor/spatie/laravel-medialibrary/config/media-library.php',
            [
                'disk_name' => 'public',
                'queue_conversions_by_default' => false,
                'queue_conversions_after_database_commit' => false,
            ]
        ));
    }

    protected function defineDatabaseMigrations(): void
    {
        //
    }
}

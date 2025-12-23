<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: $APP_BASE_PATH ?? realpath(__DIR__.'/..') ?: __DIR__.'/..')
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: ['/livewire/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

$app->useEnvironmentPath($app->basePath());

$environmentFilePath = $app->environmentFilePath();
$exampleEnvironmentPath = $app->environmentPath().DIRECTORY_SEPARATOR.'.env.example';

if (! file_exists($environmentFilePath) && file_exists($exampleEnvironmentPath)) {
    $app->loadEnvironmentFrom('.env.example');
}

$sessionPath = $app->storagePath('framework/sessions');

if (! is_dir($sessionPath)) {
    mkdir($sessionPath, 0o755, true);
}

return $app;

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

arch('it will not use debugging functions')
    ->expect([
        'dd',
        'dump',
        'var_dump',
        'Illuminate\Support\Facades\Log',
        'echo',
        'logger',
    ])
    ->each->not->toBeUsed();

arch('it uses strict typing everywhere')
    ->expect('VendorName\\Skeleton')
    ->toUseStrictTypes();

test('it will not point to dependency development versions', function () {
    expect(File::get(__DIR__.'/../composer.json'))
        ->not
        ->toContain('dev-');
});

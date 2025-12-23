<?php

declare(strict_types=1);

it('exercises the workbench home page demo', function () {
    $page = visit('/')->assertNoJavaScriptErrors();

    $page->assertSee('Hello World');
});

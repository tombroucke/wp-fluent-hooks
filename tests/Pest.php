<?php

use Otomaties\WpFluentHooks\FilterRepository;

function resetFilterRepository(): void
{
    $reflection = new ReflectionClass(FilterRepository::class);
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
}

uses()->beforeEach(function () {
    resetFilterRepository();
    Brain\Monkey\setUp();
})->afterEach(function () {
    Brain\Monkey\tearDown();
    Mockery::close();
    resetFilterRepository();
})->in(__DIR__.'/Unit');

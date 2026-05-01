<?php

use Otomaties\WpFluentHooks\Action;
use Otomaties\WpFluentHooks\Filter;

it('creates an action via hook factory method', function () {
    $action = Action::hook('init');

    expect($action)->toBeInstanceOf(Action::class)
        ->and($action->getHookName())->toBe('init');
});

it('extends Filter', function () {
    $action = Action::hook('init');

    expect($action)->toBeInstanceOf(Filter::class);
});

it('registers an action callback', function () {
    Brain\Monkey\Functions\expect('add_filter')
        ->once()
        ->with('init', Mockery::type('callable'), 10, 1);

    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('init_idx');

    $action = Action::hook('init')->register(fn () => null);

    expect($action)->toBeInstanceOf(Action::class);
});

it('supports all fluent methods from Filter', function () {
    Brain\Monkey\Functions\expect('add_filter')
        ->once()
        ->with('wp_loaded', Mockery::type('callable'), 99, 1);

    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('wp_loaded_idx');

    $action = Action::hook('wp_loaded')
        ->priority(99)
        ->alias('my_action')
        ->register(fn () => null);

    expect($action->getHookName())->toBe('wp_loaded')
        ->and($action->getPriority())->toBe(99)
        ->and($action->getAlias())->toBe('my_action');
});

it('deregisters an action by alias', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    Action::hook('init')->alias('my_init')->register(fn () => null);

    expect(Action::deregister('my_init'))->toBeTrue();
});

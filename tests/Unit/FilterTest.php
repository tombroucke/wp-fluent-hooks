<?php

use Otomaties\WpFluentHooks\Filter;

it('creates a filter via hook factory method', function () {
    $filter = Filter::hook('the_content');

    expect($filter)->toBeInstanceOf(Filter::class)
        ->and($filter->getHookName())->toBe('the_content');
});

it('has default priority of 10', function () {
    $filter = Filter::hook('the_content');

    expect($filter->getPriority())->toBe(10);
});

it('has default args of 1', function () {
    $filter = Filter::hook('the_content');

    expect($filter->getArgs())->toBe(1);
});

it('has no alias by default', function () {
    $filter = Filter::hook('the_content');

    expect($filter->getAlias())->toBeNull();
});

it('sets priority via fluent interface', function () {
    $filter = Filter::hook('the_content')->priority(20);

    expect($filter->getPriority())->toBe(20);
});

it('sets args via fluent interface', function () {
    $filter = Filter::hook('the_content')->args(3);

    expect($filter->getArgs())->toBe(3);
});

it('sets alias via fluent interface', function () {
    $filter = Filter::hook('the_content')->alias('my_alias');

    expect($filter->getAlias())->toBe('my_alias');
});

it('returns self from fluent methods', function () {
    $filter = Filter::hook('the_content');

    expect($filter->priority(5))->toBe($filter)
        ->and($filter->args(2))->toBe($filter)
        ->and($filter->alias('test'))->toBe($filter);
});

it('registers a callable and returns self', function () {
    Brain\Monkey\Functions\expect('add_filter')
        ->once()
        ->with('the_content', Mockery::type('callable'), 10, 1);

    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')
        ->once()
        ->andReturn('abc123');

    $callback = fn ($content) => $content;
    $filter = Filter::hook('the_content')->register($callback);

    expect($filter)->toBeInstanceOf(Filter::class);
});

it('stores the alias after registration', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('abc123');

    $filter = Filter::hook('the_content')->alias('my_filter')->register(fn () => null);

    expect($filter->getAlias())->toBe('my_filter');
});

it('uses auto-generated key as alias when no alias set', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('auto_idx_123');

    $filter = Filter::hook('the_content')->register(fn () => null);

    expect($filter->getAlias())->toBe('auto_idx_123');
});

it('finds a registered alias and returns a Filter instance', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    Filter::hook('the_content')->priority(5)->alias('removable')->register(fn () => null);

    $filter = Filter::findByAlias('removable');

    expect($filter)->toBeInstanceOf(Filter::class)
        ->and($filter->getHookName())->toBe('the_content')
        ->and($filter->getPriority())->toBe(5)
        ->and($filter->getAlias())->toBe('removable');
});

it('throws when finding an unknown alias', function () {
    Filter::findByAlias('does_not_exist');
})->throws(\InvalidArgumentException::class, "Alias 'does_not_exist' not found in FilterRepository.");

it('deregisters a repository-tracked filter via findByAlias', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    Filter::hook('the_content')->alias('removable2')->register(fn () => null);

    $result = Filter::findByAlias('removable2')->deregister();

    expect($result)->toBeTrue();
});

it('deregisters an arbitrary WP filter by callback', function () {
    Brain\Monkey\Functions\expect('remove_filter')
        ->once()
        ->with('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10)
        ->andReturn(true);

    $result = Filter::hook('woocommerce_single_product_summary')->deregister('woocommerce_template_single_price');

    expect($result)->toBeTrue();
});

it('supports chaining priority, args, alias, and register', function () {
    Brain\Monkey\Functions\expect('add_filter')
        ->once()
        ->with('save_post', Mockery::type('callable'), 5, 2);

    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('chained_idx');

    $filter = Filter::hook('save_post')
        ->priority(5)
        ->args(2)
        ->alias('my_save_post')
        ->register(fn ($post_id, $post) => null);

    expect($filter->getHookName())->toBe('save_post')
        ->and($filter->getPriority())->toBe(5)
        ->and($filter->getArgs())->toBe(2)
        ->and($filter->getAlias())->toBe('my_save_post');
});

it('registers a string callback', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    $filter = Filter::hook('the_title')->register('strtoupper');

    expect($filter)->toBeInstanceOf(Filter::class);
});

it('registers an array callback', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    $filter = Filter::hook('the_title')->register(['class' => 'MyClass', 'method' => 'handle']);

    expect($filter)->toBeInstanceOf(Filter::class);
});

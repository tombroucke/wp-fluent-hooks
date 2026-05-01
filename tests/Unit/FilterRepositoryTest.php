<?php

use Otomaties\WpFluentHooks\FilterRepository;

it('returns a singleton instance', function () {
    $a = FilterRepository::getInstance();
    $b = FilterRepository::getInstance();

    expect($a)->toBe($b);
});

it('starts with an empty filters list', function () {
    expect(FilterRepository::getInstance()->all())->toBe([]);
});

it('adds a filter and returns the alias', function () {
    Brain\Monkey\Functions\expect('add_filter')
        ->once()
        ->with('the_content', Mockery::type('callable'), 10, 1);

    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')
        ->once()
        ->andReturn('the_content_idx');

    $repo = FilterRepository::getInstance();
    $key = $repo->add('the_content', fn ($c) => $c, 10, 1, 'my_alias');

    expect($key)->toBe('my_alias');
});

it('stores filter data after add', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    $repo = FilterRepository::getInstance();
    $repo->add('the_title', fn ($t) => $t, 5, 1, 'title_filter');

    $all = $repo->all();

    expect($all)->toHaveKey('title_filter')
        ->and($all['title_filter']['hookName'])->toBe('the_title')
        ->and($all['title_filter']['priority'])->toBe(5)
        ->and($all['title_filter']['idx'])->toBe('idx');
});

it('uses auto-generated idx as key when no alias provided', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('auto_idx');

    $repo = FilterRepository::getInstance();
    $key = $repo->add('the_content', fn () => null, 10, 1, null);

    expect($key)->toBe('auto_idx')
        ->and($repo->all())->toHaveKey('auto_idx');
});

it('throws InvalidArgumentException when alias is already in use', function () {
    Brain\Monkey\Functions\expect('add_filter')->twice();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->twice()->andReturn('idx1', 'idx2');

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn () => null, 10, 1, 'duplicate_alias');

    $repo->add('the_title', fn () => null, 10, 1, 'duplicate_alias');
})->throws(InvalidArgumentException::class, "Alias 'duplicate_alias' is already in use.");

it('removes a filter by alias', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn () => null, 10, 1, 'removable');

    $result = $repo->remove('removable');

    expect($result)->toBeTrue()
        ->and($repo->all())->not->toHaveKey('removable');
});

it('returns false when removing non-existent alias', function () {
    $result = FilterRepository::getInstance()->remove('ghost');

    expect($result)->toBeFalse();
});

it('removes the callback from wp_filter global', function () {
    global $wp_filter;

    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('my_idx');

    // Simulate wp_filter holding the callback
    $wpFilterEntry = new stdClass();
    $wpFilterEntry->callbacks = [10 => ['my_idx' => ['function' => fn () => null]]];
    $wp_filter['the_content'] = $wpFilterEntry;

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn () => null, 10, 1, 'removable');
    $repo->remove('removable');

    expect(isset($wp_filter['the_content']->callbacks[10]['my_idx']))->toBeFalse();
});

it('tracks multiple filters', function () {
    Brain\Monkey\Functions\expect('add_filter')->times(3);
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->times(3)->andReturn('idx1', 'idx2', 'idx3');

    $repo = FilterRepository::getInstance();
    $repo->add('hook_a', fn () => null, 10, 1, 'alias_a');
    $repo->add('hook_b', fn () => null, 10, 1, 'alias_b');
    $repo->add('hook_c', fn () => null, 10, 1, 'alias_c');

    expect($repo->all())->toHaveCount(3)
        ->toHaveKeys(['alias_a', 'alias_b', 'alias_c']);
});

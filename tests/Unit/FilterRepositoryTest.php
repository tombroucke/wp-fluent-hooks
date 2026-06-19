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

it('adds a filter and stores it in the repository', function () {
    Brain\Monkey\Functions\expect('add_filter')
        ->once()
        ->with('the_content', Mockery::type('callable'), 10, 1);

    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')
        ->once()
        ->andReturn('the_content_idx');

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn ($c) => $c, 10, 1, 'my_alias');

    expect($repo->all())->toHaveKey('the_content_my_alias_10');
});

it('stores filter data after add', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    $repo = FilterRepository::getInstance();
    $repo->add('the_title', fn ($t) => $t, 5, 1, 'title_filter');

    $all = $repo->all();

    expect($all)->toHaveKey('the_title_title_filter_5')
        ->and($all['the_title_title_filter_5']['hookName'])->toBe('the_title')
        ->and($all['the_title_title_filter_5']['priority'])->toBe(5)
        ->and($all['the_title_title_filter_5']['idx'])->toBe('idx');
});

it('does not store filter in repository when no alias provided', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn () => null, 10, 1, null);

    expect($repo->all())->toBe([]);
});

it('throws InvalidArgumentException when alias is already in use', function () {
    Brain\Monkey\Functions\expect('add_filter')->twice();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->twice()->andReturn('idx1', 'idx2');

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn () => null, 10, 1, 'duplicate_alias');

    $repo->add('the_content', fn () => null, 10, 1, 'duplicate_alias');
})->throws(InvalidArgumentException::class, "Alias 'duplicate_alias' is already in use.");

it('finds a filter by hook name, alias and priority', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn () => null, 10, 1, 'my_alias');

    expect($repo->find('the_content', 'my_alias', 10))->toBeArray()
        ->and($repo->find('the_content', 'my_alias', 99))->toBeNull()
        ->and($repo->find('other_hook', 'my_alias', 10))->toBeNull();
});

it('removes a filter by hook name, alias and priority', function () {
    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('idx');

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn () => null, 10, 1, 'removable');

    $result = $repo->remove('the_content', 'removable', 10);

    expect($result)->toBeTrue()
        ->and($repo->all())->not->toHaveKey('the_content_removable_10');
});

it('returns false when removing non-existent alias', function () {
    $result = FilterRepository::getInstance()->remove('the_content', 'ghost', 10);

    expect($result)->toBeFalse();
});

it('removes the callback from wp_filter global', function () {
    global $wp_filter;

    Brain\Monkey\Functions\expect('add_filter')->once();
    Brain\Monkey\Functions\expect('_wp_filter_build_unique_id')->once()->andReturn('my_idx');

    $wpFilterEntry = new stdClass;
    $wpFilterEntry->callbacks = [10 => ['my_idx' => ['function' => fn () => null]]];
    $wp_filter['the_content'] = $wpFilterEntry;

    $repo = FilterRepository::getInstance();
    $repo->add('the_content', fn () => null, 10, 1, 'removable');
    $repo->remove('the_content', 'removable', 10);

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
        ->toHaveKeys(['hook_a_alias_a_10', 'hook_b_alias_b_10', 'hook_c_alias_c_10']);
});

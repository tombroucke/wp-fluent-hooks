## Examples

### Filters

Basic usage:

```php
Filter::hook('the_title')
    ->register(fn ($title) => strtoupper($title));
```

With priority and argument count:

```php
Filter::hook('save_post')
    ->args(3) // Default 1
    ->priority(11) // Default 10
    ->register(function ($postId, $post, $update) {
        // Do something
    });
```

### Actions

`Action` and `Filter` share the same API and can be used interchangeably.

```php
Action::hook('init')
    ->register(function () {
        // Do something
    });
```

### Aliases

Assign an alias to reference the hook later:

```php
Action::hook('body_class')
    ->alias('my_custom_body_class')
    ->register(fn ($classes) => array_merge($classes, ['custom-class']));
```

### Deregistering

`deregister()` is fluent and can be chained. The hook name and priority must match the original registration.

If the hook was registered via this library, it will be removed via the repository:

```php
// With an explicit alias
Action::hook('body_class')
    ->deregister('my_custom_body_class')
    ->register(fn ($classes) => array_merge($classes, ['custom-class']));

// Without an alias — use the auto-generated one
$filter = Filter::hook('the_title')
    ->register(fn ($title) => strtoupper($title));

Filter::hook('the_title')->deregister($filter->getAlias());
```

To deregister a hook added externally via `add_filter()` or `add_action()`, pass the callback name and match the priority:

```php
Action::hook('woocommerce_before_main_content')
    ->priority(20)
    ->deregister('woocommerce_breadcrumb')
    ->register(function () {
        yoast_breadcrumb('<p class="small breadcrumb">', '</p>');
    });
```

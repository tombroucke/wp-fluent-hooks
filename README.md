# WP Fluent Hooks

A fluent interface for registering and deregistering WordPress filters and actions. Instead of repeating the hook name across multiple `add_filter()` and `remove_filter()` calls, you chain everything together. This makes your hook logic easier to read and maintain.

```php
// Before
remove_filter('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_filter('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

// After
Action::hook('woocommerce_before_shop_loop')
    ->deregister('woocommerce_result_count', 20)
    ->deregister('woocommerce_catalog_ordering', 30);
```

## Examples

### Filters

```php
Filter::hook('the_title')
    ->register(fn ($title) => strtoupper($title));
```

With priority and argument count:

```php
Filter::hook('save_post')
    ->register(function ($postId, $post, $update) {
        // Do something
    }, priority: 11, args: 3);
```

### Actions

`Action` and `Filter` share the same API and can be used interchangeably.

```php
Action::hook('init')
    ->register(function () {
        // Do something
    });
```

### Deregistering

Deregister external hooks by passing the callback name and priority inline:

```php
Action::hook('woocommerce_before_shop_loop')
    ->deregister('woocommerce_result_count', 20)
    ->deregister('woocommerce_catalog_ordering', 30);
```

Chain deregistering and registering on the same hook. Use chainable methods `priority()` and `args()` to set values for the rest of the chain:

```php
Action::hook('woocommerce_before_main_content')
    ->priority(20)
    ->deregister('woocommerce_breadcrumb')
    ->register(function () {
        yoast_breadcrumb('<p class="small breadcrumb">', '</p>');
    });
```

### Aliases

Only hooks registered with an alias are tracked internally. Without an alias, the hook is registered with WordPress but cannot be deregistered via this library. Assign an alias to reference a hook registered via this library:

```php
Action::hook('body_class')
    ->alias('my_custom_body_class')
    ->register(fn ($classes) => array_merge($classes, ['custom-class']));

// Later:
Action::hook('body_class')
    ->deregister('my_custom_body_class');
```

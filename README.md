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

Remove a registered hook by its alias:

```php
Action::deregister('my_custom_body_class');
```

If you didn't define an alias, retrieve the auto-generated one from the registered instance:

```php
$filter = Filter::hook('the_title')
    ->register(fn ($title) => strtoupper($title));

$alias = $filter->getAlias();

Filter::deregister($alias);
```

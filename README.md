## Examples

### Filters

```php
Filter::hook('the_title')
    ->register(fn ($title) => strtoupper($title));
```

```php
Filter::hook('save_post')
    ->args(3)
    ->priority(11)
    ->register(function ($postId, $post, $update) {
        // Do something
    });
```

### Actions

```php
Action::hook('init')
    ->register(function () {
        // Do something
    });
```

### Aliases

Assign an alias to reference the filter later:

```php
Action::hook('body_class')
    ->alias('my_custom_body_class')
    ->register(fn ($classes) => array_merge($classes, ['custom-class']));
```

### Deregistering

By instance:

```php
$action = Action::hook('body_class')
    ->alias('my_custom_body_class')
    ->register(fn ($classes) => array_merge($classes, ['custom-class']));

$action->deregister();
```

By finding the instance:

```php
Action::find('my_custom_body_class')?->deregister();
```

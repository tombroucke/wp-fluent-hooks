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

### HasHooks trait

Use the `HasHooks` trait in your own classes to register string method names as hook callbacks:

```php
use Otomaties\WpFluentHooks\HasHooks;

class WooCommerce
{
    use HasHooks;

    public function woocommerce_template_single_price(): void
    {
        // Custom price output
    }
}
```

Then from a service provider or bootstrap file:

```php
// Remove the default WooCommerce hook
Filter::hook('woocommerce_single_product_summary')
    ->priority(10) // 10 is default, so in this case this is actually not needed
    ->deregister('woocommerce_template_single_price');

// Register your custom class method at a different priority
WooCommerce::hook('woocommerce_single_product_summary')
    ->priority(25)
    ->register('woocommerce_template_single_price');
```

### Deregistering

Remove a hook that was registered through this library using its alias:

```php
Action::findByAlias('my_custom_body_class')->deregister();
```

If you didn't define an alias, use the auto-generated one from the registered instance:

```php
$filter = Filter::hook('the_title')
    ->register(fn ($title) => strtoupper($title));

Filter::findByAlias($filter->getAlias())->deregister();
```

Remove a hook that was **not** registered through this library by passing the callback directly:

```php
Filter::hook('woocommerce_single_product_summary')
    ->priority(10)
    ->deregister('woocommerce_template_single_price');
```

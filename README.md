## Examples

```php
Filter::hook('save_post')
    ->args(3)
    ->priority(11)
    ->register(function ($postId, $post, $update) {
        // Do something
    });
```

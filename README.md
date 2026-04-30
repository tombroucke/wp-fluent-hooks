## Examples

```php
Filter::on('wp_resource_hints')
	->args(2)
	->do(function (array $urls, string $relationType) {
		if ($relationType === 'dns-prefetch') {
            $emojiSvgUrl = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
            $urls = array_diff($urls, [$emojiSvgUrl]);
        }

        return $urls;
	});
```

<?php

namespace Otomaties\WpFluentHooks;

trait HasHooks
{
    public static function hook(string $hookName): Filter
    {
        return Filter::hook($hookName, static::class);
    }
}

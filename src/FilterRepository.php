<?php

namespace Otomaties\WpFluentHooks;

class FilterRepository
{
    protected static ?self $instance = null;

    /** @var array<string, array{hookName: string, priority: int, idx: string}> */
    protected array $filters = [];

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function add(string $hookName, callable|string|array $callback, int $priority, int $args, ?string $alias): string
    {
        add_filter($hookName, $callback, $priority, $args);

        $idx = _wp_filter_build_unique_id($hookName, $callback, $priority);
        $key = $alias ?? $idx;

        if ($alias !== null && isset($this->filters[$alias])) {
            throw new \InvalidArgumentException("Alias '{$alias}' is already in use.");
        }

        $this->filters[$key] = compact('hookName', 'priority', 'idx');

        return $key;
    }

    public function remove(string $alias): bool
    {
        global $wp_filter;

        if (! isset($this->filters[$alias])) {
            return false;
        }

        ['hookName' => $hookName, 'priority' => $priority, 'idx' => $idx] = $this->filters[$alias];

        if (isset($wp_filter[$hookName]->callbacks[$priority][$idx])) {
            unset($wp_filter[$hookName]->callbacks[$priority][$idx]);
        }

        unset($this->filters[$alias]);

        return true;
    }

    /** @return array<string, array{hookName: string, priority: int, idx: string}> */
    public function all(): array
    {
        return $this->filters;
    }
}

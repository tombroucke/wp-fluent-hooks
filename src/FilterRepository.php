<?php

namespace Otomaties\WpFluentHooks;

final class FilterRepository
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

    /**
    * @param callable|string|array{class: string, method: string} $callback
    */
    public function add(string $hookName, callable|string|array $callback, int $priority, int $args, ?string $alias): ?string
    {
        /** @var callable $callable */
        $callable = $callback;
        add_filter($hookName, $callable, $priority, $args);

        $idx = _wp_filter_build_unique_id($hookName, $callback, $priority);
        $alias = $alias ?? $idx;

        if (!$alias || !$idx) {
            return null;
        }

        $key = $this->buildKey($hookName, $alias, $priority);

        if (isset($this->filters[$key])) {
            throw new \InvalidArgumentException("Alias '{$alias}' is already in use.");
        }

        $this->filters[$key] = compact('hookName', 'priority', 'idx');

        return $alias;
    }

    /** @return array{hookName: string, priority: int, idx: string}|null */
    public function find(string $hookName, string $alias, int $priority): ?array
    {
        $key = $this->buildKey($hookName, $alias, $priority);

        return $this->filters[$key] ?? null;
    }

    public function remove(string $hookName, string $alias, int $priority): bool
    {
        global $wp_filter;

        $key = $this->buildKey($hookName, $alias, $priority);

        if (! isset($this->filters[$key])) {
            return false;
        }

        ['priority' => $priority, 'idx' => $idx] = $this->filters[$key];

        if (isset($wp_filter[$hookName]->callbacks[$priority][$idx])) {
            unset($wp_filter[$hookName]->callbacks[$priority][$idx]);
        }

        unset($this->filters[$key]);

        return true;
    }

    /** @return array<string, array{hookName: string, priority: int, idx: string}> */
    public function all(): array
    {
        return $this->filters;
    }

    private function buildKey(string $hookName, string $alias, int $priority): string
    {
        return "{$hookName}_{$alias}_{$priority}";
    }
}

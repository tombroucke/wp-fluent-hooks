<?php

namespace Otomaties\WpFluentHooks;

class Filter
{
    protected int $priority = 10;

    protected int $args = 1;

    protected ?string $alias = null;

    final protected function __construct(protected string $hookName, private mixed $object = null)
    {
        //
    }

    public static function hook(string $hookName, mixed $object = null): static
    {
        return new static($hookName, $object);
    }

    public function priority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function args(int $args): static
    {
        $this->args = $args;

        return $this;
    }

    public function alias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param  callable|string|array{class: string, method: string}  $callback
     */
    public function register(callable|string|array $callback): static
    {
        if (is_string($callback) && $this->object !== null && method_exists($this->object, $callback)) {
            $callback = [$this->object, $callback];
        }

        $this->alias = FilterRepository::getInstance()->add($this->hookName, $callback, $this->priority, $this->args, $this->alias);

        return $this;
    }

    public static function findByAlias(string $alias): static
    {
        $entry = FilterRepository::getInstance()->get($alias);

        if ($entry === null) {
            throw new \InvalidArgumentException("Alias '{$alias}' not found in FilterRepository.");
        }

        return static::hook($entry['hookName'])->priority($entry['priority'])->alias($alias);
    }

    public function deregister(callable|string|array $callback = null): bool
    {
        if ($callback !== null) {
            return remove_filter($this->hookName, $callback, $this->priority);
        }

        return FilterRepository::getInstance()->remove($this->alias);
    }

    public function getHookName(): string
    {
        return $this->hookName;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getArgs(): int
    {
        return $this->args;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}

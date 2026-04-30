<?php

namespace Otomaties\WpFluentHooks;

class Filter
{
    protected int $priority = 10;

    protected int $args = 1;

    protected ?string $alias = null;

    protected ?string $key = null;

    /** @var array<string, static> */
    protected static array $instances = [];

    protected function __construct(protected string $hookName)
    {
        //
    }

    public static function hook(string $hookName): static
    {
        return new static($hookName);
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

    public function register(callable|string|array $callback): static
    {
        $this->key = FilterRepository::getInstance()->add($this->hookName, $callback, $this->priority, $this->args, $this->alias);
        static::$instances[$this->key] = $this;

        return $this;
    }

    public function deregister(): bool
    {
        $result = FilterRepository::getInstance()->remove($this->key);
        unset(static::$instances[$this->key]);

        return $result;
    }

    public static function find(string $key): ?static
    {
        return static::$instances[$key] ?? null;
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

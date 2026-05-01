<?php

namespace Otomaties\WpFluentHooks;

class Filter
{
    protected int $priority = 10;

    protected int $args = 1;

    protected ?string $alias = null;

    final protected function __construct(protected string $hookName)
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

    /**
     * @param callable|string|array{class: string, method: string} $callback
     */
    public function register(callable|string|array $callback): static
    {
        $this->alias = FilterRepository::getInstance()->add($this->hookName, $callback, $this->priority, $this->args, $this->alias);

        return $this;
    }

    public static function deregister(string $alias): bool
    {
        return FilterRepository::getInstance()->remove($alias);
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

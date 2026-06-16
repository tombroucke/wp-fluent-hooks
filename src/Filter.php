<?php

namespace Otomaties\WpFluentHooks;

class Filter
{
    protected int $priority = 10;

    protected int $args = 1;

    protected ?string $alias = null;

    /** @var callable|null */
    protected $when = null;

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

    public function when(callable $condition): static
    {
        $this->when = $condition;

        return $this;
    }

    public function always(): static
    {
        $this->when = null;

        return $this;
    }

    /**
     * @param  callable|string|array{class: string, method: string}  $callback
     */
    public function register(callable|string|array $callback, ?int $priority = null, ?int $args = null): static
    {
        $priority = $priority ?? $this->priority;
        $args = $args ?? $this->args;

        if ($this->when !== null) {
            $condition = $this->when;
            $callable = function () use ($condition, $callback) {
                $filterArgs = func_get_args();

                if (!$condition(...$filterArgs)) {
                    return $filterArgs[0];
                }

                return $callback(...$filterArgs);
            };
        } else {
            $callable = $callback;
        }

        FilterRepository::getInstance()->add($this->hookName, $callable, $priority, $args, $this->alias);

        return $this;
    }

    public function deregister(string $callback, ?int $priority = null): static
    {
        if ($this->when !== null) {
            $reflection = new \ReflectionFunction(\Closure::fromCallable($this->when));
            if ($reflection->getNumberOfParameters() > 0) {
                throw new \InvalidArgumentException('The condition passed to when() may not have parameters when used with deregister().');
            }

            if (!($this->when)()) {
                return $this;
            }
        }

        $priority = $priority ?? $this->priority;

        if (FilterRepository::getInstance()->find($this->hookName, $callback, $priority)) {
            FilterRepository::getInstance()->remove($this->hookName, $callback, $priority);
        } else {
            remove_filter($this->hookName, $callback, $priority);
        }

        return $this;
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

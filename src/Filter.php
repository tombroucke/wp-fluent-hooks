<?php

namespace Otomaties\WpFluentHooks;

class Filter
{
    protected int $priority = 10;

    protected int $args = 1;

    protected ?string $alias = null;

    protected ?string $atHook = null;

    protected int $atPriority = 10;

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

    public function at(string $hookName, int $priority = 10): static
    {
        $this->atHook = $hookName;
        $this->atPriority = $priority;

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

        if ($this->atHook !== null) {
            add_action($this->atHook, function () use ($callable, $priority, $args) {
                FilterRepository::getInstance()->add($this->hookName, $callable, $priority, $args, $this->alias);
            }, $this->atPriority);

            return $this;
        }

        FilterRepository::getInstance()->add($this->hookName, $callable, $priority, $args, $this->alias);

        return $this;
    }

    public function deregister(string $callback, ?int $priority = null): static
    {
        if ($this->when !== null) {
            throw new \LogicException('when() cannot be used with deregister(). Use at() to defer deregistration to a hook where the condition can be evaluated.');
        }

        $priority = $priority ?? $this->priority;

        if ($this->atHook !== null) {
            add_action($this->atHook, function () use ($callback, $priority) {
                $this->performDeregister($callback, $priority);
            }, $this->atPriority);

            return $this;
        }

        $this->performDeregister($callback, $priority);

        return $this;
    }

    protected function performDeregister(string $callback, int $priority): void
    {
        if (FilterRepository::getInstance()->find($this->hookName, $callback, $priority)) {
            FilterRepository::getInstance()->remove($this->hookName, $callback, $priority);
        } else {
            remove_filter($this->hookName, $callback, $priority);
        }
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

    public function getAtHook(): ?string
    {
        return $this->atHook;
    }

    public function getAtPriority(): int
    {
        return $this->atPriority;
    }
}

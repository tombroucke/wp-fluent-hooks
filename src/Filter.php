<?php

namespace Otomaties\WpFluentHooks;

class Filter
{
    protected int $priority = 10;

    protected int $args = 1;

    public function __construct(protected string $actionName)
    {
        //
    }

    public static function hook(string $actionName): self
    {
        return new self($actionName);
    }

    public function priority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function args(int $args): self
    {
        $this->args = $args;

        return $this;
    }

    public function register(callable $callback): self
    {
        add_filter($this->actionName, $callback, $this->priority, $this->args);

        return $this;
    }
}

<?php

namespace WP_Mock\Hooks;

use Closure;

/**
 * Object representation of an invoked filter value.
 */
class InvokedFilterValue
{
    /** @var callable|Closure|callable-string */
    protected $callback;

    /**
     * Constructor.
     *
     * @param callable|callable-string|Closure $callable
     */
    public function __construct($callable)
    {
        $this->callback = $callable;
    }

    /**
     * Invokes the filter value.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}

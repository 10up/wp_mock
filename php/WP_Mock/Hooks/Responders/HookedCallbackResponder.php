<?php

namespace WP_Mock\Hooks\Responders;

use Closure;

/**
 * Hooked callback responder.
 */
class HookedCallbackResponder extends Responder
{
    /** @var callable|Closure */
    protected $callable;

    /**
     * Sets the callback.
     *
     * @param callable|Closure $callable
     * @return $this
     */
    public function perform($callable) : Responder
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * Reacts to hook.
     *
     * @return mixed
     */
    public function react()
    {
        return call_user_func($this->callable);
    }
}

<?php

namespace WP_Mock\Hooks\Responders;

use Closure;
use WP_Mock\Hooks\Action;
use WP_Mock\Hooks\Hook;

/**
 * Action hook responder.
 *
 * @see Action
 * @see Hook
 */
class ActionResponder extends Responder
{
    /** @var callable|Closure */
    protected $callable;

    /**
     * @param callable|Closure $callable
     * @return void
     */
    public function perform($callable) : void
    {
        $this->callable = $callable;
    }

    /**
     * Reacts to action hook.
     *
     * @return void
     */
    public function react() : void
    {
        call_user_func($this->callable);
    }
}

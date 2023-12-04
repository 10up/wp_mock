<?php

namespace WP_Mock\Hooks\Responders;

use WP_Mock\Hooks\InvokedFilterValue;

/**
 * Filter hook responder.
 *
 * @see Filter
 * @see Hook
 */
class FilterResponder extends Responder
{
    /** @var mixed */
    protected $value;

    /**
     * Sets the filtered value.
     *
     * @param mixed $value
     * @return void
     */
    public function reply($value)
    {
        $this->value = $value;
    }

    /**
     * Sends the filtered value.
     *
     * @return mixed
     */
    public function send()
    {
        if ($this->value instanceof InvokedFilterValue) {
            return call_user_func_array($this->value, func_get_args());
        }

        return $this->value;
    }
}

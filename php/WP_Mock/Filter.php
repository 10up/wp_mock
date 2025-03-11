<?php

namespace WP_Mock;

/**
 * Mock representation of a WordPress filter as an object.
 *
 * Mocks WordPress filters by substituting each filter with an object capable of intercepting calls and returning predictable behavior.
 */
class Filter extends Hook
{
    /** @var array<mixed> Collection of filter names mapped to random integers. */
    protected static array $filtersWithAnyArgs = [];

    /**
     * Apply the stored filter.
     *
     * @param array $args Arguments passed to apply_filters()
     *
     * @return mixed
     */
    public function apply($args)
    {
        if (isset(static::$filtersWithAnyArgs[ $this->name ])) {
            $args = array_values(static::$filtersWithAnyArgs);
        }

        if ($args[0] === null && count($args) === 1) {
            if (isset($this->processors['argsnull'])) {
                return $this->processors['argsnull']->send();
            }
            $this->strict_check();

            return null;
        }

        $processors = $this->processors;
        foreach ($args as $arg) {
            $key = $this->safe_offset($arg);
            if (! is_array($processors) || ! isset($processors[ $key ])) {
                $this->strict_check();

                return $arg;
            }

            $processors = $processors[ $key ];
        }

        return call_user_func_array(array($processors, 'send'), $args);
    }

    /**
     * @return Filter_Responder
     */
    protected function new_responder()
    {
        return new Filter_Responder();
    }

    /**
     * @return string
     */
    protected function get_strict_mode_message()
    {
        return sprintf('Unexpected use of apply_filters for filter %s', $this->name);
    }

    /**
     * @return Action_Responder|Filter_Responder|HookedCallbackResponder
     */
    public function withAnyArgs()
    {
        $random_value = mt_rand();
        static::$filtersWithAnyArgs[ $this->name ] = $random_value;

        return $this->with($random_value);
    }
}

class Filter_Responder
{
    /**
     * @var mixed
     */
    protected $value;

    public function reply($value)
    {
        $this->value = $value;
    }

    public function send()
    {
        if ($this->value instanceof InvokedFilterValue) {
            return call_user_func_array($this->value, func_get_args());
        }

        return $this->value;
    }
}

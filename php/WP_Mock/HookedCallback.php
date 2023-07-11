<?php

namespace WP_Mock;

use WP_Mock\Matcher\AnyInstance;

class HookedCallback extends Hook
{
    protected $type = 'filter';
    protected $callback;

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function react($callback, $priority, $argument_count)
    {
        \WP_Mock::addHook($this->name, $this->type);

        $safe_callback = $this->safe_offset($callback);

        if (is_array($callback)) {
            $any_instance_callback = array( new AnyInstance($callback[0]), $callback[1] );
            $safe_any_instance_callback = $this->safe_offset($any_instance_callback);
            if (! empty($this->processors[ $safe_any_instance_callback ])) {
                $safe_callback = $safe_any_instance_callback;
            }
        }

        if (
            empty($this->processors[ $safe_callback ]) ||
            empty($this->processors[ $safe_callback ][ $priority ]) ||
            empty($this->processors[ $safe_callback ][ $priority ][ $argument_count ])
        ) {
            $this->callback = $callback;
            $this->strict_check();

            return null;
        }

        return $this->processors[ $safe_callback ][ $priority ][ $argument_count ]->react();
    }

    protected function new_responder()
    {
        return new HookedCallbackResponder();
    }

    /**
     * Converts a callable to a string
     *
     * Closures get returned as 'Closure', objects (those with an __invoke() method get turned into <Class>::__invoke,
     * and arrays get turned into <Class>::<method>
     *
     * @param callable $callback
     *
     * @return string
     */
    protected function callback_to_string($callback)
    {
        if (! is_string($callback)) {
            if ($callback instanceof \Closure) {
                $callback = 'Closure';
            } elseif (is_object($callback)) {
                $callback = get_class($callback) . '::__invoke';
            } else {
                $class  = $callback[0];
                $method = $callback[1];
                if (! is_string($class)) {
                    $class = get_class($class);
                }
                $callback = "{$class}::$method";
            }
        }

        return $callback;
    }

    /**
     * @param $callback
     *
     * @return string
     */
    protected function get_strict_mode_message()
    {
        return sprintf(
            'Unexpected use of add_%s for action %s with callback %s',
            $this->type,
            $this->name,
            $this->callback_to_string($this->callback)
        );
    }
}

class HookedCallbackResponder
{
    /**
     * @var callable
     */
    protected $callable;

    public function perform($callable)
    {
        $this->callable = $callable;
    }

    public function react()
    {
        call_user_func($this->callable);
    }
}

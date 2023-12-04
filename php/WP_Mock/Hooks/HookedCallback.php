<?php

namespace WP_Mock\Hooks;

use Closure;
use Exception;
use ReflectionException;
use WP_Mock\Hooks\Responders\HookedCallbackResponder;
use WP_Mock\Hooks\Responders\Responder;
use WP_Mock\Matcher\AnyInstance;

/**
 * Hooked callback representation.
 *
 * @property array<int|string, array<int, array<int, HookedCallbackResponder>>> $processors
 */
class HookedCallback extends Hook
{
    /** @var string */
    protected string $type = 'filter';

    /** @var callable|Closure|callable-string */
    protected $callback;

    /**
     * Sets the hook type.
     *
     * @param string $type one of 'action' or 'filter' (defaults to 'filter')
     * @return $this
     */
    public function setType(string $type) : Hook
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Reacts to the hook.
     *
     * @param callable|Closure $callback
     * @param int $priority
     * @param int $argumentCount
     * @return mixed
     * @throws ReflectionException|Exception
     */
    public function react($callback, int $priority, int $argumentCount)
    {
        \WP_Mock::addHook($this->name, $this->type);

        $safe_callback = $this->safe_offset($callback);

        if (is_array($callback)) {
            $anyInstanceCallback = [new AnyInstance($callback[0]), $callback[1]];
            $safeAnyInstanceCallback = $this->safe_offset($anyInstanceCallback);

            if (! empty($this->processors[$safeAnyInstanceCallback])) {
                $safe_callback = $safeAnyInstanceCallback;
            }
        }

        if (
            empty($this->processors[$safe_callback]) ||
            empty($this->processors[$safe_callback][$priority]) ||
            empty($this->processors[$safe_callback][$priority][$argumentCount])
        ) {
            $this->callback = $callback;
            $this->strict_check();

            return null;
        }

        $responder = $this->processors[$safe_callback][$priority][$argumentCount];

        return $responder->react();
    }

    /**
     * Instantiates a new responder for the hook.
     *
     * @return HookedCallbackResponder
     */
    protected function getResponderInstance() : Responder
    {
        return new HookedCallbackResponder();
    }

    /**
     * Converts a callable to a string.
     *
     * - Closures get returned as 'Closure'.
     * - Objects with their invoke method get returned as <Class>::__invoke.
     * - Callable arrays get turned into <Class>::<method>
     *
     * @param string|Closure|object|array<mixed> $callback
     * @return string
     */
    protected function callbackToString($callback) : string
    {
        if (! is_string($callback)) {
            if ($callback instanceof Closure) {
                $callback = 'Closure';
            } elseif (is_object($callback)) {
                $callback = get_class($callback) . '::__invoke';
            } else {
                /** @var object|class-string $class */
                $class  = $callback[0];
                /** @var string $method */
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
     * Gets the strict mode error message.
     *
     * @return string
     */
    protected function getStrictModeErrorMessage() : string
    {
        /** @var object|callable-string|array<string, string> $callback */
        $callback = $this->callback;

        return sprintf(
            'Unexpected use of add_%s for action %s with callback %s',
            $this->type,
            $this->name,
            $this->callbackToString($callback)
        );
    }
}

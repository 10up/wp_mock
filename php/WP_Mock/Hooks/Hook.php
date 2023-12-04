<?php

namespace WP_Mock\Hooks;

use Closure;
use Mockery\Matcher\Type;
use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock;
use WP_Mock\Hooks\Responders\ActionResponder;
use WP_Mock\Hooks\Responders\FilterResponder;
use WP_Mock\Hooks\Responders\HookedCallbackResponder;
use WP_Mock\Hooks\Responders\Responder;
use WP_Mock\Matcher\AnyInstance;

/**
 * Abstract mock representation of a WordPress hook.
 *
 * @see Action for mocking WordPress action hooks
 * @see Filter for mocking WordPress filter hooks
 */
abstract class Hook
{
    /** @var string hook name */
    protected string $name;

    /** @var array<int|string, Responder|array<int, array<int, Responder>>> $processors collection of processors */
    protected array $processors = [];

    /**
     * Hook constructor.
     *
     * @param string $name hook name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Gets a string representation of a value.
     *
     * @param mixed $value
     * @return string
     */
    protected function safe_offset($value): string
    {
        if (null === $value) {
            return 'null';
        }

        /**
         * The following is to prevent a possible return mismatch when {@see Functions::type()} is used with `callable`,
         * and to correctly create safe offsets for processors when expecting that a hook that uses a closure is added via {@see Functions::type(Closure::class)}.
         */
        $closure = fn () => null;
        if ($value instanceof Closure || Closure::class === $value || (is_string($value) && '<CLOSURE>' === strtoupper($value)) || ($value instanceof Type && $value->match($closure))) {
            return '__CLOSURE__';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if ($value instanceof AnyInstance) {
            return (string) $value;
        }

        if (is_object($value)) {
            return spl_object_hash($value);
        }

        if (is_array($value)) {
            $parsed = '';

            foreach ($value as $k => $v) {
                $k = is_numeric($k) ? '' : $k;
                $parsed .= $k.$this->safe_offset($v);
            }

            return $parsed;
        }

        return '';
    }

    /**
     * Returns the expected responder for the hook.
     *
     * @return ActionResponder|FilterResponder|HookedCallbackResponder
     */
    public function with() : Responder
    {
        $args      = func_get_args();
        $responder = $this->getResponderInstance();

        if ($args === array( null )) {
            $this->processors['argsnull'] = $responder;
        } else {
            $numArgs = count($args);

            /** @var array<int, array<int, array<int, Responder>>> $processors */
            $processors = &$this->processors;

            for ($i = 0; $i < $numArgs - 1; $i ++) {
                /** @var int $arg */
                $arg = $this->safe_offset($args[$i]);

                if (! isset($processors[$arg])) {
                    /** @phpstan-ignore-next-line */
                    $processors[$arg] = [];
                }

                /** @var array<int, array<int, array<int, Responder>>> $processors */
                $processors = &$processors[$arg];
            }

            $processors[$this->safe_offset($args[$numArgs - 1])] = $responder;
        }

        /** @var ActionResponder|FilterResponder|HookedCallbackResponder $responder */
        return $responder;
    }

    /**
     * Instantiates a new responder for the hook.
     *
     * @return Responder
     */
    abstract protected function getResponderInstance() : Responder;

    /**
     * Throws an exception if strict mode is on.
     *
     * @return void
     * @throws ExpectationFailedException
     */
    protected function strict_check(): void
    {
        if (WP_Mock::strictMode()) {
            throw new ExpectationFailedException($this->getStrictModeErrorMessage());
        }
    }

    /**
     * Gets the message to output when the strict mode exception is thrown.
     *
     * @return string
     */
    abstract protected function getStrictModeErrorMessage() : string;
}

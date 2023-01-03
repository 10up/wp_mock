<?php

namespace WP_Mock;

use Closure;
use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock;
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
    protected $name;

    /** @var array<mixed> collection of processors */
    protected $processors = [];

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
        /** the following is to prevent a possible return mismatch when {@see \WP_Mock\Functions::type()} is used with 'callable' */
        } elseif ($value instanceof Closure || Closure::class === $value || (is_string($value) && '<CLOSURE>' === strtoupper($value))) {
            return '__CLOSURE__';
        } elseif (is_scalar($value)) {
            return (string) $value;
        } elseif ($value instanceof AnyInstance) {
            return (string) $value;
        } elseif (is_object($value)) {
            return spl_object_hash($value);
        } elseif (is_array($value)) {
            $parsed = '';

            foreach ($value as $k => $v) {
                $k = is_numeric($k) ? '' : $k;
                $parsed .= $k.$this->safe_offset($v);
            }

            return $parsed;
        }

        return '';
    }

    /** @return Action_Responder|Filter_Responder */
    public function with()
    {
        $args      = func_get_args();
        $responder = $this->new_responder();

        if ($args === array( null )) {
            $this->processors['argsnull'] = $responder;
        } else {
            $num_args = count($args);

            $processors = &$this->processors;
            for ($i = 0; $i < $num_args - 1; $i ++) {
                $arg = $this->safe_offset($args[ $i ]);

                if (! isset($processors[ $arg ])) {
                    $processors[ $arg ] = array();
                }

                $processors = &$processors[ $arg ];
            }

            $processors[ $this->safe_offset($args[ $num_args - 1 ]) ] = $responder;
        }

        return $responder;
    }

    abstract protected function new_responder();

    /**
     * Throws an exception if strict mode is on.
     *
     * @return void
     * @throws ExpectationFailedException
     */
    protected function strict_check(): void
    {
        if (WP_Mock::strictMode()) {
            throw new ExpectationFailedException($this->get_strict_mode_message());
        }
    }

    /**
     * Gets the message to output when the strict mode exception is thrown.
     *
     * @return string
     */
    abstract protected function get_strict_mode_message();
}

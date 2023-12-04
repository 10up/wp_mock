<?php

namespace WP_Mock\Hooks;

use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock\Hooks\Responders\FilterResponder;
use WP_Mock\Hooks\Responders\Responder;

/**
 * Mock representation of a WordPress filter as an object.
 *
 * Mocks WordPress filters by substituting each filter with an object capable of intercepting calls and returning predictable behavior.
 *
 * @property array<int|string, array<int, array<int, FilterResponder>>> $processors
 */
class Filter extends Hook
{
    /**
     * Applies the stored filter.
     *
     * @param array<mixed> $args Arguments passed to apply_filters()
     * @return mixed
     * @throws ExpectationFailedException
     */
    public function apply(array $args = [])
    {
        if (count($args) === 1 && $args[0] === null) {
            if (isset($this->processors['argsnull'])) {
                /** @var FilterResponder $responder */
                $responder = $this->processors['argsnull'];
                return $responder->send();
            }

            $this->strict_check();

            return null;
        }

        $processors = $this->processors;

        foreach ($args as $arg) {
            $key = $this->safe_offset($arg);

            if (! is_array($processors) || ! isset($processors[$key])) {
                $this->strict_check();

                return $arg;
            }

            $processors = $processors[$key];
        }

        /** @var callable $send */
        $send = [$processors, 'send'];

        return call_user_func_array($send, $args);
    }

    /**
     * Gets the responder for the current filter.
     *
     * @return FilterResponder
     */
    protected function getResponderInstance() : Responder
    {
        return new FilterResponder();
    }

    /**
     * Gets the strict mode error message.
     *
     * @return string
     */
    protected function getStrictModeErrorMessage() : string
    {
        return sprintf('Unexpected use of apply_filters for filter %s', $this->name);
    }
}

<?php

namespace WP_Mock\Hooks;

use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock\Hooks\Responders\ActionResponder;
use WP_Mock\Hooks\Responders\Responder;

/**
 * Mock representation of a WordPress action as an object.
 *
 * Mocks WordPress actions by substituting each action with an object capable of intercepting calls and returning predictable behavior.
 *
 * @property array<int|string, array<int, array<int, ActionResponder>>> $processors
 */
class Action extends Hook
{
    /**
     * Reacts to the stored action.
     *
     * @param array<mixed> $args
     * @return void
     * @throws ExpectationFailedException
     */
    public function react(array $args = []) : void
    {
        \WP_Mock::invokeAction($this->name);

        $arg_num = count($args);

        if (0 === $arg_num) {
            if (! isset($this->processors['argsnull'])) {
                $this->strict_check();

                return;
            }

            /** @var ActionResponder $responder */
            $responder = $this->processors['argsnull'];
            $responder->react();
        } else {
            /** @var ActionResponder[] $processors */
            $processors = $this->processors;

            for ($i = 0; $i < $arg_num - 1; $i ++) {
                $arg = $this->safe_offset($args[$i]);

                if (! isset($processors[$arg])) {
                    $this->strict_check();

                    return;
                }

                $processors = $processors[$arg];
            }

            $arg = $this->safe_offset($args[$arg_num - 1]);

            if (! is_array($processors) || ! isset($processors[$arg])) {
                $this->strict_check();

                return;
            }

            $processors[$arg]->react();
        }
    }

    /**
     * Instantiates a new responder for the action hook.
     *
     * @return ActionResponder
     */
    protected function getResponderInstance() : Responder
    {
        return new ActionResponder();
    }

    /**
     * Gets the strict mode error message.
     *
     * @return string
     */
    protected function getStrictModeErrorMessage() : string
    {
        return sprintf('Unexpected use of do_action for action %s', $this->name);
    }
}

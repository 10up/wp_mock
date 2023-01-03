<?php

namespace WP_Mock\Tools\Constraints;

use PHPUnit\Framework\Constraint\Constraint;
use Mockery;
use Exception;

/**
 * Expectations-met constraint.
 */
class ExpectationsMet extends Constraint
{
    /** @var string */
    private $failureDescription;

    /**
     * Evaluates the constraint for parameter $other.
     *
     * Returns true if the constraint is met, false otherwise.
     *
     * @param mixed $other
     * @return bool
     */
    public function matches($other): bool
    {
        try {
            Mockery::getContainer()->mockery_verify();
        } catch (Exception $exception) {
            $this->failureDescription = $exception->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return 'WP_Mock expectations are met';
    }

    /**
     * Gets the additional failure description.
     *
     * @param mixed $other
     * @return string
     */
    protected function additionalFailureDescription($other): string
    {
        return str_replace(["\r", "\n"], '', $this->failureDescription);
    }

    /**
     * Gets the failure description.
     *
     * @param mixed $other
     * @return string
     */
    protected function failureDescription($other): string
    {
        return $this->toString();
    }
}

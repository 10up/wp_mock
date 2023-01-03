<?php

namespace WP_Mock\Tools\Constraints;

use Exception;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * HTML string constraint.
 */
class IsEqualHtml extends Constraint
{
    /** @var string */
    protected $value;

    /** @var float */
    private $delta;

    /** @var bool */
    private $canonicalize;

    /** @var bool */
    private $ignoreCase;

    /**
     * Constructor.
     *
     * @param string $value
     * @param float $delta
     * @param bool $canonicalize
     * @param bool $ignoreCase
     */
    public function __construct(string $value, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false)
    {
        $this->value = $value;
        $this->delta = $delta;
        $this->canonicalize = $canonicalize;
        $this->ignoreCase = $ignoreCase;
    }

    /**
     * Trims and removes tabs, newlines and return carriages from a string.
     *
     * @param string $value
     * @return string
     */
    protected function clean(string $value): string
    {
        $value = preg_replace('/\n\s+/', '', $value) ?: '';
        $value = preg_replace('/\s\s+/', ' ', $value) ?: '';

        return str_replace(array( "\r", "\n", "\t" ), '', $value);
    }

    /**
     * Evaluates the constraint for parameter $other.
     *
     * If $returnResult is false (default), an exception is thrown in case of a failure. null is returned otherwise.
     * If $returnResult is true, the result of the evaluation is returned as a boolean instead, based on success or failure.
     *
     * @param string $other value to evaluate
     * @param string $description message used in failures
     * @param bool $returnResult whether to throw an exception in case of failure or return boolean
     * @return bool|null
     * @throws ExpectationFailedException
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        $other = $this->clean($other);
        $this->value = $this->clean($this->value);

        $isEqual = new IsEqual($this->value, $this->delta, $this->canonicalize, $this->ignoreCase);
        $result = $isEqual->evaluate($other, $description, $returnResult);

        return $returnResult ? $result : null;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @see Constraint::toString()
     *
     * @return string
     * @throws Exception
     */
    public function toString(): string
    {
        $isEqual = new IsEqual($this->value, $this->delta, $this->canonicalize, $this->ignoreCase);

        return 'html '.$isEqual->toString();
    }
}

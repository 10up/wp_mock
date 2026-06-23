<?php

namespace WP_Mock\Tools\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

/**
 * HTML string constraint.
 *
 * Compares two HTML strings for equality while ignoring insignificant whitespace
 * (tabs, newlines, carriage returns and collapsed runs of whitespace).
 */
class IsEqualHtml extends Constraint
{
    /** @var string */
    protected $value;

    /**
     * Constructor.
     *
     * @param string $value the expected HTML
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Evaluates whether $other equals the expected HTML, ignoring insignificant whitespace.
     *
     * @param mixed $other value to evaluate (untyped for PHP 7.4 compatibility; the parent declares `mixed` on PHPUnit 10+)
     * @return bool
     */
    public function matches($other): bool
    {
        $actual = is_scalar($other) ? (string) $other : '';

        return $this->clean($actual) === $this->clean($this->value);
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @see Constraint::toString()
     *
     * @return string
     */
    public function toString(): string
    {
        // Note: do NOT use Constraint::exporter() here — it was removed in PHPUnit 11.0.
        return sprintf("html is equal to '%s'", $this->clean($this->value));
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

        return str_replace(["\r", "\n", "\t"], '', $value);
    }
}

<?php

namespace WP_Mock\Tools\Constraints;

use PHPUnit\Framework\Constraint\IsEqual;

class IsEqualHtml {
	protected $IsEqual;
	protected $value;

	/**
	 * @var float
	 */
	private $delta;

	/**
	 * @var int
	 */
	private $maxDepth;

	/**
	 * @var bool
	 */
	private $canonicalize;

	/**
	 * @var bool
	 */
	private $ignoreCase;

	public function __construct(
			$value,
			float $delta = 0.0,
			int $maxDepth = 10,
			bool $canonicalize = false,
			bool $ignoreCase = false
		) {
			$this->value = $value;
			$this->delta = $delta;
			$this->maxDepth = $maxDepth;
			$this->canonicalize = $canonicalize;
			$this->ignoreCase = $ignoreCase;
		}

	private function clean( $thing ) {
		$thing = preg_replace( '/\n\s+/', '', $thing );
		$thing = preg_replace( '/\s\s+/', ' ', $thing );
		return str_replace( array( "\r", "\n", "\t" ), '', $thing );
	}

	public function evaluate( $other, $description = '', $returnResult = FALSE ) {
		$other       = $this->clean( $other );
		$this->value = $this->clean( $this->value );
		$isEqual = new IsEqual( $this->value, $this->delta, $this->maxDepth, $this->canonicalize, $this->ignoreCase );
		return $isEqual->evaluate( $other, $description, $returnResult );
	}
}

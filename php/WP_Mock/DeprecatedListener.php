<?php

namespace WP_Mock;

use \PHPUnit\Framework\TestCase;

class DeprecatedListener {

	protected $calls = array();

	/** @var \PHPUnit\Framework\TestCase */
	protected $testResult;

	protected $testName;

	/**
	 * @var \PHPUnit\Framework\TestCase
	 */
	protected $testCase;

	public function logDeprecatedCall( $method, array $args = array() ) {
		$this->calls[] = array( $method, $args );
	}

	public function reset() {
		$this->calls = array();
	}

	public function checkCalls() {
		if ( empty( $this->calls ) ) {
			return;
		}
		$e = new \PHPUnit\Framework\RiskyTestError( $this->getMessage() );
		$this->testResult->addFailure( $this->testCase, $e, 0 );
	}

	/**
	 * @param \PHPUnit\Framework\TestResult $testResult
	 */
	public function setTestResult( $testResult ) {
		$this->testResult = $testResult;
	}

	/**
	 * @param mixed $testName
	 */
	public function setTestName( $testName ) {
		$this->testName = $testName;
	}

	public function setTestCase( \PHPUnit\Framework\TestCase $testCase ) {
		$this->testCase = $testCase;
	}

	protected function getMessage() {
		$maxLength = array_reduce( $this->getDeprecatedMethods(), function ( $carry, $item ) {
				return max( $carry, strlen( $item ) );
			}, 0 ) + 1;
		$message   = 'Deprecated WP Mock calls inside ' . $this->testName . ":";
		foreach ( $this->getDeprecatedMethodsWithArgs() as $method => $args ) {
			$firstRun = true;
			$extra    = $maxLength - strlen( $method );
			foreach ( $args as $arg ) {
				$message .= "\n  ";
				if ( $firstRun ) {
					$message .= $method . str_repeat( ' ', $extra );
					$firstRun = false;
					$extra    = $maxLength;
				} else {
					$message .= str_repeat( ' ', $extra );
				}
				$message .= $arg;
			}
		}

		return $message;
	}

	protected function getDeprecatedMethods() {
		$methods = array();
		foreach ( $this->calls as $call ) {
			$methods[] = $call[0];
		}

		return array_unique( $methods );
	}

	protected function getDeprecatedMethodsWithArgs() {
		$collection = array();
		foreach ( $this->calls as $call ) {
			$method = $call[0];
			$args   = json_encode( array_map( array( $this, 'scalarizeArg' ), $call[1] ) );
			if ( empty( $collection[ $method ] ) ) {
				$collection[ $method ] = array();
			}
			$collection[ $method ][] = $args;
		}

		return array_map( 'array_unique', $collection );
	}

	protected function scalarizeArg( $value ) {
		if ( is_scalar( $value ) ) {
			return $value;
		} elseif ( is_object( $value ) ) {
			return '<' . get_class( $value ) . ':' . spl_object_hash( $value ) . '>';
		} elseif ( is_array( $value ) ) {
			if ( is_callable( $value ) ) {
				return '[' . implode( ',', array_map( array( $this, 'scalarizeArg' ), $value ) ) . ']';
			} else {
				return 'Array([' . count( $value ) . '] ...)';
			}
		} elseif ( is_resource( $value ) ) {
			return 'Resource';
		} else {
			return 'Unknown Value';
		}
	}
}

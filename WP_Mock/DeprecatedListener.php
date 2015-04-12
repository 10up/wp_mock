<?php

namespace WP_Mock;

class DeprecatedListener {

	protected $calls = array();

	/** @var \PHPUnit_Framework_TestResult */
	protected $testResult;

	public function logDeprecatedCall( $method, array $args = array() ) {
		$this->calls[] = array( $method, $args );
	}

	public function reset() {
		$this->calls = array();
	}

	public function checkCalls() {

	}

	/**
	 * @param \PHPUnit_Framework_TestResult $testResult
	 */
	public function setTestResult( $testResult ) {
		$this->testResult = $testResult;
	}
}

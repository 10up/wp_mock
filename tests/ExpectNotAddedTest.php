<?php

use WP_Mock\Tools\TestCase;

class ExpectNotAddedTest extends TestCase {

	public $callback;

	public function setUp() {
		parent::setUp();

		$this->callback = array( $this, 'handler' );
	}

	/* expected failure */
	public function test_expect_action_not_added_fails_if_action_was_added() {
		WP_Mock::expectActionNotAdded(
			'foo_action', $this->callback
		);

		add_action( 'foo_action', array( $this, 'handler' ) );
	}

	public function test_expect_action_not_added_does_not_fail_if_action_was_not_added() {
		WP_Mock::expectActionNotAdded(
			'foo_action', $this->callback
		);
	}

	/* expected failure */
	public function test_expect_filter_not_added_fails_if_filter_was_added() {
		WP_Mock::expectFilterNotAdded(
			'foo_filter', $this->callback
		);

		add_filter( 'foo_filter', array( $this, 'handler' ) );
	}

	public function test_expect_filter_not_added_does_not_fail_if_filter_was_not_added() {
		WP_Mock::expectFilterNotAdded(
			'foo_filter', $this->callback
		);
	}

	/* helpers */
	public function handler() {

	}

}

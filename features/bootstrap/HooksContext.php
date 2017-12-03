<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;

class HooksContext implements Context {

	private $filterResults = array();

	/**
	 * @BeforeScenario
	 */
	public function setUpWpMock( BeforeScenarioScope $scope ) {
		$this->filterResults = array();
	}

	/**
	 * @AfterScenario
	 */
	public function tearDownWpMock( AfterScenarioScope $scope ) {
		$this->filterResults = array();
	}

	/**
	 * @Given I expect the following actions added:
	 */
	public function iExpectTheFollowingActionsAdded( TableNode $table ) {
		foreach ( $this->getActionsWithDefaults( $table ) as $action ) {
			WP_Mock::expectActionAdded(
				$action['action'],
				$action['callback'],
				$action['priority'],
				$action['arguments']
			);
		}
	}

	/**
	 * @Given I expect the following actions not to be added:
	 */
	public function iExpectTheFollowingActionsNotToBeAdded( TableNode $table ) {
		foreach ( $this->getActionsWithDefaults( $table ) as $action ) {
			WP_Mock::expectActionNotAdded( $action['action'], $action['callback'] );
		}
	}

	/**
	 * @Given I expect the :action action
	 */
	public function iExpectTheAction( $action ) {
		$this->iExpectTheActionWith( $action, new TableNode( array() ) );
	}

	/**
	 * @When I expect the :action action with:
	 */
	public function iExpectTheActionWith( $action, TableNode $table ) {
		$args = array( $action );
		$rows = $table->getRows();
		if ( isset( $rows[0] ) && is_array( $rows[0] ) ) {
			$args = array_merge( $args, $rows[0] );
		}
		call_user_func_array( array( 'WP_Mock', 'expectAction' ), $args );
	}

	/**
	 * @Given I expect the :filter filter with :value
	 */
	public function iExpectTheFilterWith( $filter, $value ) {
		$this->iExpectTheFilterWithValues( $filter, new TableNode( array( array( $value ) ) ) );
	}

	/**
	 * @When I expect the :filter filter with:
	 */
	public function iExpectTheFilterWithValues( $filter, TableNode $table ) {
		$args = array( $filter );
		$rows = $table->getRows();
		if ( isset( $rows[0] ) && is_array( $rows[0] ) ) {
			$args = array_merge( $args, $rows[0] );
		}
		call_user_func_array( array( 'WP_Mock', 'expectFilter' ), $args );
	}

	/**
	 * @When I add the following actions:
	 */
	public function iAddTheFollowingActions( TableNode $table ) {
		foreach ( $this->getActionsWithDefaults( $table ) as $action ) {
			add_action(
				$action['action'],
				$action['callback'],
				$action['priority'],
				$action['arguments']
			);
		}
	}

	/**
	 * @When I do the :action action
	 */
	public function iDoTheAction( $action ) {
		$this->iDoTheActionWith( $action, new TableNode( array() ) );
	}

	/**
	 * @When I do the :action action with:
	 */
	public function iDoTheActionWith( $action, TableNode $table ) {
		$args = array( $action );
		$rows = $table->getRows();
		if ( isset( $rows[0] ) && is_array( $rows[0] ) ) {
			$args = array_merge( $args, $rows[0] );
		}
		call_user_func_array( 'do_action', $args );
	}

	/**
	 * @Given I expect the following filters added:
	 */
	public function iExpectTheFollowingFiltersAdded( TableNode $table ) {
		$filters  = $table->getHash();
		$defaults = array(
			'filter'    => '',
			'callback'  => '',
			'priority'  => 10,
			'arguments' => 1,
		);
		foreach ( $filters as $filter ) {
			$filter += $defaults;
			WP_Mock::expectFilterAdded(
				$filter['filter'],
				$filter['callback'],
				$filter['priority'],
				$filter['arguments']
			);
		}
	}

	/**
	 * @Given I expect the following filters not to be added:
	 */
	public function iExpectTheFollowingFiltersNotToBeAdded( TableNode $table ) {
		foreach ( $this->getFiltersWithDefaults( $table ) as $filter ) {
			WP_Mock::expectFilterNotAdded( $filter['filter'], $filter['callback'] );
		}
	}

	/**
	 * @When I add the following filters:
	 */
	public function iAddTheFollowingFilters( TableNode $table ) {
		foreach ( $this->getFiltersWithDefaults( $table ) as $filter ) {
			add_filter(
				$filter['filter'],
				$filter['callback'],
				$filter['priority'],
				$filter['arguments']
			);
		}
	}

	/**
	 * @Given I expect filter :filter to respond to :thing with :response
	 */
	public function iExpectFilterToRespondToWith( $filter, $thing, $response ) {
		WP_Mock::onFilter( $filter )->with( $thing )->reply( $response );
	}

	/**
	 * @Given I expect filter :filter to respond with :response
	 */
	public function iExpectFilterToRespondWith( $filter, $response ) {
		$this->iExpectFilterToRespondToWith( $filter, null, $response );
	}

	/**
	 * @When I apply the filter :filter with :with
	 */
	public function iApplyFilterWith( $filter, $with ) {
		$this->iApplyFilterWithData( $filter, new TableNode( array( array( $with ) ) ) );
	}

	/**
	 * @When I apply the filter :filter with:
	 */
	public function iApplyFilterWithData( $filter, TableNode $table ) {
		$row = $table->getRow( 0 );
		array_unshift( $row, $filter );
		$this->filterResults[ $filter ] = call_user_func_array( 'apply_filters', $row );
	}

	/**
	 * @Then The filter :filter should return :value
	 */
	public function theFilterShouldReturn( $filter, $value ) {
		\PHPUnit\Framework\Assert::assertArrayHasKey( $filter, $this->filterResults );
		\PHPUnit\Framework\Assert::assertEquals( $this->filterResults[ $filter ], $value );
	}

	private function getActionsWithDefaults( TableNode $table ) {
		$actions  = $table->getHash();
		$defaults = array(
			'action'    => '',
			'callback'  => '',
			'priority'  => 10,
			'arguments' => 1,
		);
		foreach ( $actions as &$action ) {
			$action += $defaults;
		}
		unset( $action );

		return $actions;
	}

	private function getFiltersWithDefaults( TableNode $table ) {
		$filters  = $table->getHash();
		$defaults = array(
			'filter'    => '',
			'callback'  => '',
			'priority'  => 10,
			'arguments' => 1,
		);
		foreach ( $filters as &$filter ) {
			$filter += $defaults;
		}
		unset( $filter );

		return $filters;
	}

}

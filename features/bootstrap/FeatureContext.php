<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @BeforeScenario
     */
    public function setUpWpMock(BeforeScenarioScope $scope)
    {
        WP_Mock::setUp();
    }

    /**
     * @AfterScenario
     */
    public function tearDownWpMock(AfterScenarioScope $scope)
    {
        WP_Mock::tearDown();
    }

    /**
     * @Given I expect the following actions added:
     */
    public function iExpectTheFollowingActions(TableNode $table)
    {
        $actions  = $table->getHash();
        $defaults = array(
            'action'    => '',
            'callback'  => '',
            'priority'  => 10,
            'arguments' => 1,
        );
        foreach ($actions as $action) {
            $action += $defaults;
            WP_Mock::expectActionAdded(
                $action['action'],
                $action['callback'],
                $action['priority'],
                $action['arguments']
            );
        }
    }

    /**
     * @When I add the following actions:
     */
    public function iAddTheFollowingActions(TableNode $table)
    {
        $actions  = $table->getHash();
        $defaults = array(
            'action'    => '',
            'callback'  => '',
            'priority'  => 10,
            'arguments' => 1,
        );
        foreach ($actions as $action) {
            $action += $defaults;
            add_action(
                $action['action'],
                $action['callback'],
                $action['priority'],
                $action['arguments']
            );
        }
    }

    /**
     * @Given I expect the following filters added:
     */
    public function iExpectTheFollowingFilters(TableNode $table)
    {
        $filters  = $table->getHash();
        $defaults = array(
            'filter'    => '',
            'callback'  => '',
            'priority'  => 10,
            'arguments' => 1,
        );
        foreach ($filters as $filter) {
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
     * @When I add the following filters:
     */
    public function iAddTheFollowingFilters(TableNode $table)
    {
        $filters  = $table->getHash();
        $defaults = array(
            'filter'    => '',
            'callback'  => '',
            'priority'  => 10,
            'arguments' => 1,
        );
        foreach ($filters as $filter) {
            $filter += $defaults;
            add_filter(
                $filter['filter'],
                $filter['callback'],
                $filter['priority'],
                $filter['arguments']
            );
        }
    }

    /**
     * @Then tearDown should not fail
     */
    public function teardownShouldNotFail()
    {
        WP_Mock::tearDown();
    }

    /**
     * @When I do nothing
     */
    public function iDoNothing()
    {
        // Move along...
    }

    /**
     * @Then tearDown should fail
     */
    public function teardownShouldFail()
    {
        try {
            $this->teardownShouldNotFail();
            throw new PHPUnit_Framework_ExpectationFailedException('WP_Mock Teardown should have failed!');
        } catch (\Mockery\Exception\InvalidCountException $e) {
            // Move along
        }
    }
}

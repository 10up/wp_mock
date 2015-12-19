<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
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
     * @Given I expect the following actions added:
     */
    public function iExpectTheFollowingActions(TableNode $table)
    {
        throw new \Behat\Behat\Tester\Exception\PendingException();
    }

    /**
     * @When I add the following actions:
     */
    public function iAddTheFollowingActions(TableNode $table)
    {
        throw new \Behat\Behat\Tester\Exception\PendingException();
    }

    /**
     * @Then tearDown should not fail
     */
    public function teardownShouldNotFail()
    {
        throw new \Behat\Behat\Tester\Exception\PendingException();
    }
}

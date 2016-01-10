<?php

use Behat\Behat\Context\Context;

class FunctionsContext implements Context
{

    /**
     * @Given function :function does not exist
     */
    public function functionDoesNotExist($function)
    {
        PHPUnit_Framework_Assert::assertFalse(function_exists($function));
    }

    /**
     * @When I mock function :function
     */
    public function iMockFunction($function)
    {
        WP_Mock::userFunction($function);
    }

    /**
     * @Then function :function should exist
     */
    public function functionShouldExist($function)
    {
        PHPUnit_Framework_Assert::assertTrue(function_exists($function));
    }

}

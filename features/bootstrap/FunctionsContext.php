<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Mockery\Exception\NoMatchingExpectationException;

class FunctionsContext implements Context
{

    private $storedReturn;

    /**
     * @Given function :function does not exist
     */
    public function functionDoesNotExist($function)
    {
        PHPUnit_Framework_Assert::assertFalse(function_exists($function));
    }

    /**
     * @Given I mock passthru function :function with args:
     */
    public function iMockPassthruFunctionWithArgs($function, TableNode $args)
    {
        WP_Mock::passthruFunction($function, array(
            'args' => $args->getRow(0),
        ));
    }

    /**
     * @Given I mock function :function to return :value
     */
    public function iMockFunctionToReturn($function, $value)
    {
        WP_Mock::userFunction($function, array('return' => $value));
    }

    /**
     * @Given I alias :alias to :function
     */
    public function iAliasTo($alias, $function)
    {
        WP_Mock::alias($alias, $function);
    }

    /**
     * @Given I mock function :function to echo input
     */
    public function iMockFunctionWpMockTestToEcho($function)
    {
        WP_Mock::echoFunction($function);
    }

    /**
     * @When I mock function :function
     */
    public function iMockFunction($function)
    {
        WP_Mock::userFunction($function);
    }

    /**
     * @When I store the return value of function mock :function
     */
    public function iStoreTheReturnValueOfFunctionMock($function)
    {
        $this->storedReturn = WP_Mock::userFunction($function);
    }

    /**
     * @Then function :function should exist
     */
    public function functionShouldExist($function)
    {
        PHPUnit_Framework_Assert::assertTrue(function_exists($function));
    }

    /**
     * @Then I expect :return when I run :function with args:
     */
    public function iExpectWhenIRunWithArgs($return, $function, TableNode $args)
    {
        PHPUnit_Framework_Assert::assertEquals($return, call_user_func_array($function, $args->getRow(0)));
    }

    /**
     * @Then I expect :return when I run :function
     */
    public function iExcpectWhenIRun($return, $function)
    {
        $this->iExpectWhenIRunWithArgs($return, $function, new TableNode(array(array())));
    }

    /**
     * @Then I expect an error when I run :function with args:
     */
    public function iExpectAnErrorWhenIRunWithArgs($function, TableNode $args)
    {
        try {
            $this->iExpectWhenIRunWithArgs(null, $function, $args);
        } catch (NoMatchingExpectationException $e) {
            // Move along...
        }
    }

    /**
     * @Then I expect function :function to echo :input
     */
    public function iExpectFunctionToEcho($function, $input)
    {
        ob_start();
        $function($input);
        $output = trim(ob_get_clean());
        PHPUnit_Framework_Assert::assertEquals(trim($input), $output);
    }

    /**
     * @Then The stored return should be an instance of :class
     */
    public function theStoredReturnShouldBeAnInstanceOf($class)
    {
        PHPUnit_Framework_Assert::assertInstanceOf($class, $this->storedReturn);
    }

}

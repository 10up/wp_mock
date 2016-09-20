<?php

class WP_MockTest extends PHPUnit_Framework_TestCase
{

    public function test_userFunction_returns_expectation()
    {
        $this->assertInstanceOf(
            '\Mockery\ExpectationInterface',
            WP_Mock::userFunction('testWpMockFunction')
        );
    }

}

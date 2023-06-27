<?php

namespace Unit\WP_Mock\Functions;

use Exception;
use ReflectionProperty;
use WP_Mock\Functions\Handler;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock\Functions\Handler
 */
final class HandlerTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Functions\Handler::registerHandler()
     *
     * @return void
     * @throws Exception
     */
    public function testCanRegisterHandler(): void
    {
        $property = new ReflectionProperty(Handler::class, 'handlers');

        $this->assertSame([], $property->getValue());

        $functionName = 'foo';
        $callback = function () {
            return 'bar';
        };

        Handler::registerHandler($functionName, $callback);

        $this->assertSame([$functionName => $callback], $property->getValue());
    }

    public function testCanHandleFunction(): void
    {

    }

    public function testCanDetermineHandlerExists(): void
    {

    }

    public function testCanCleanup(): void
    {

    }

    public function testCanHandlePredefinedReturnFunction(): void
    {

    }

    public function testCanHandlePredefinedEchoFunction(): void
    {

    }


}

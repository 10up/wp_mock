<?php

namespace Unit\WP_Mock\Functions;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use ReflectionProperty;
use WP_Mock;
use WP_Mock\Functions\Handler;
use WP_Mock\Tests\WP_MockTestCase;

#[CoversClass(Handler::class)]
final class HandlerTest extends WP_MockTestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testCanRegisterHandler(): void
    {
        // ensures a starting clean state without having to run test in isolation
        Handler::cleanup();

        $property = new ReflectionProperty(Handler::class, 'handlers');
        $property->setAccessible(true);

        $this->assertSame([], $property->getValue());

        $functionName = 'test_function';
        $callback = function ($arg) {
            return $arg;
        };

        Handler::registerHandler($functionName, $callback);

        $this->assertEquals([$functionName => $callback], $property->getValue());

        // restore the original state
        Handler::cleanup();
    }

    /**
     * @return void
     * @throws Exception
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanHandleFunction(): void
    {
        $this->assertNull(Handler::handleFunction('invalid'));

        $functionName = 'test_function';
        $callback = function ($arg) {
            return $arg;
        };

        Handler::registerHandler($functionName, $callback);

        $this->assertEquals('test-arg', Handler::handleFunction($functionName, ['test-arg']));
    }

    /**
     * @return void
     * @throws Exception
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanDetermineHandlerExists(): void
    {
        $functionName = 'test_function';
        $callback = function () {
            return null;
        };

        $this->assertFalse(Handler::handlerExists($functionName));

        Handler::registerHandler($functionName, $callback);

        $this->assertTrue(Handler::handlerExists($functionName));
    }

    /**
     * @return void
     * @throws Exception
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanCleanup(): void
    {
        $property = new ReflectionProperty(Handler::class, 'handlers');
        $property->setAccessible(true);

        $functionName = 'test_function';
        $callback = function () {
            return null;
        };

        Handler::registerHandler($functionName, $callback);
        Handler::cleanup();

        $this->assertSame([], $property->getValue());
    }

    /**
     * @return void
     * @throws Exception
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanHandlePredefinedReturnFunction(): void
    {
        WP_Mock::bootstrap();
        WP_Mock::userFunction('test_function')
            ->once()
            ->andReturn('test-arg');

        assert(function_exists('test_function'));

        $this->assertSame('test-arg', test_function());
    }

    /**
     * @return void
     * @throws Exception
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanHandlePredefinedEchoFunction(): void
    {
        WP_Mock::bootstrap();
        WP_Mock::echoFunction('test_function');

        ob_start();

        assert(function_exists('test_function'));

        test_function('test');

        $this->assertSame('test', ob_get_clean());
    }
}

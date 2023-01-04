<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Tools;

use Exception;
use Mockery;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_Mock;
use WP_Mock\DeprecatedListener;
use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Tools\TestCase;

/**
 * @covers \WP_Mock\Tools\TestCase
 */
final class TestCaseTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Tools\TestCase::setUp()
     *
     * @return void
     * @throws Exception
     */
    public function testCanSetUpTests(): void
    {
        $_POST = 'test_post';
        $_GET = 'test_get';
        $_REQUEST = 'test_request';

        $methods = ['requireFileDependencies', 'setUpContentFiltering', 'cleanGlobals'];
        $instance = $this->getMockForAbstractClass(TestCase::class, [], '', false, false, true, $methods);

        foreach ($methods as $method) {
            $instance->expects($this->once())->method($method);
        }

        $instance->setUp();

        $this->assertSame([], $_POST);
        $this->assertSame([], $_GET);
        $this->assertSame([], $_REQUEST);
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::tearDown()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws Exception|ReflectionException
     */
    public function testCanTearDownTests(): void
    {
        $wpMock = Mockery::mock('overload:WP_Mock');
        $wpMock->shouldReceive('tearDown');

        $instance = $this->getMockForAbstractClass(TestCase::class, [], '', false, false, true, [
            'cleanGlobals',
        ]);

        $instance->expects($this->once())
            ->method('cleanGlobals');

        $property = new ReflectionProperty($instance, 'mockedStaticMethods');
        $property->setAccessible(true);
        $property->setValue($instance, ['foo' => 'bar']);

        $instance->tearDown();

        $this->assertSame([], $property->getValue($instance));
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::run()
     *
     * @doesNotPerformAssertions
     *
     * @return void
     * @throws Exception
     */
    public function testCanRunTests(): void
    {
        $this->markTestSkipped('Cannot create test doubles for final classes from PHPUnit.');
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::after()
     *
     * @return void
     * @throws Exception
     */
    public function testCanPerformLogicAfterTests(): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class, [], '', false, false, true, [
            'checkDeprecatedCalls',
        ]);

        $instance->expects($this->once())
            ->method('checkDeprecatedCalls');

        $instance->after();
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::checkDeprecatedCalls()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws Exception
     */
    public function testCanCheckDeprecatedCalls(): void
    {
        $deprecatedListener = $this->getMockBuilder(DeprecatedListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkCalls', 'reset'])
            ->getMock();

        $deprecatedListener->expects($this->atMost(1))
            ->method('checkCalls');

        $deprecatedListener->expects($this->atMost(1))
            ->method('reset');

        $wpMock = Mockery::mock('overload:WP_Mock');
        $wpMock->shouldReceive('getDeprecatedListener')
            ->andReturn($deprecatedListener);

        $instance = $this->getMockForAbstractClass(TestCase::class);
        $method = new ReflectionMethod($instance, 'checkDeprecatedCalls');
        $method->invoke($instance);
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::cleanGlobals()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanCleanGlobals() : void
    {
        global $post, $wp_query;

        $post = 'foo';
        $wp_query = 'bar';

        $instance = $this->getMockForAbstractClass(TestCase::class);
        $method = new ReflectionMethod($instance, 'cleanGlobals');
        $method->invoke($instance);

        $this->assertNull($GLOBALS['post'] ?? null);
        $this->assertNull($GLOBALS['wp_query'] ?? null);
    }
}

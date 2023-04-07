<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Tools;

use Exception;
use Generator;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_Mock;
use WP_Mock\DeprecatedMethodListener;
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
        $deprecatedMethodListener = $this->getMockBuilder(DeprecatedMethodListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkCalls', 'reset'])
            ->getMock();

        $deprecatedMethodListener->expects($this->atMost(1))
            ->method('checkCalls');

        $deprecatedMethodListener->expects($this->atMost(1))
            ->method('reset');

        /** @var Mockery\Mock $wpMock */
        $wpMock = Mockery::mock('overload:WP_Mock');
        /** @phpstan-ignore-next-line  */
        $wpMock->shouldReceive('getDeprecatedMethodListener')
            ->andReturn($deprecatedMethodListener);

        $instance = $this->getMockForAbstractClass(TestCase::class);
        $method = new ReflectionMethod($instance, 'checkDeprecatedCalls');
        $method->setAccessible(true);
        $method->invoke($instance);
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::cleanGlobals()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanCleanGlobals(): void
    {
        global $post, $wp_query;

        $post = 'foo';
        $wp_query = 'bar';

        $instance = $this->getMockForAbstractClass(TestCase::class);
        $method = new ReflectionMethod($instance, 'cleanGlobals');
        $method->setAccessible(true);
        $method->invoke($instance);

        $this->assertNull($GLOBALS['post'] ?? null);
        $this->assertNull($GLOBALS['wp_query'] ?? null);
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::setUpContentFiltering()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanSetUpContentFiltering(): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class);

        $property = new ReflectionProperty($instance, '__contentFilterCallback');
        $property->setAccessible(true);

        $this->assertFalse($property->getValue($instance));

        $method = new ReflectionMethod($instance, 'setUpContentFiltering');
        $method->setAccessible(true);
        $method->invoke($instance);

        $this->assertSame([$instance, 'stripTabsAndNewlines'], $property->getValue($instance));
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::stripTabsAndNewlines()
     *
     * @return void
     * @throws Exception
     */
    public function testCanStripTabsAndNewlinesForContentFiltering(): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class);

        $this->assertSame('Test', $instance->stripTabsAndNewlines("\n\n\tTest\r\t"));
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::assertActionsCalled()
     * @dataProvider providerAssertActionsCalled
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param bool $throwsException
     * @return void
     * @throws Exception
     */public function testCanAssertExpectedActionsWereCalled(bool $throwsException): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class);

        /** @var Mockery\Mock $wpMock */
        $wpMock = Mockery::mock('overload:WP_Mock');
        $method = $wpMock->shouldReceive('assertActionsCalled');

        if ($throwsException) {
            /** @phpstan-ignore-next-line  */
            $method->andThrow(Exception::class);

            $this->expectException(ExpectationFailedException::class);
        }

        $instance->assertActionsCalled();
    }

    /** @see testCanAssertExpectedActionsWereCalled */
    public function providerAssertActionsCalled(): Generator
    {
        yield 'Actions were not called' => [true];
        yield 'Actions were called' => [false];
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::assertHooksAdded()
     * @dataProvider providerAssertHooksAdded
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param bool $throwsException
     * @return void
     * @throws Exception
     */
    public function testCanAssertExpectedHooksWereAdded(bool $throwsException): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class);

        /** @var Mockery\Mock $wpMock */
        $wpMock = Mockery::mock('overload:WP_Mock');
        $method = $wpMock->shouldReceive('assertHooksAdded');

        if ($throwsException) {
            /** @phpstan-ignore-next-line  */
            $method->andThrow(Exception::class);

            $this->expectException(ExpectationFailedException::class);
        }

        $instance->assertHooksAdded();
    }

    /** @see testCanAssertExpectedHooksWereAdded */
    public function providerAssertHooksAdded(): Generator
    {
        yield 'Hooks were not added' => [true];
        yield 'Hooks were added' => [false];
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::assertCurrentConditionsMet()
     *
     * @return void
     * @throws Exception
     */
    public function testCanAssertCurrentTestConditionsWereMet(): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class, [], '', true, true, true, [
            'assertConditionsMet'
        ]);

        $instance->expects($this->once())
            ->method('assertConditionsMet')
            ->with('test');

        $instance->assertCurrentConditionsMet('test');
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::assertConditionsMet()
     *
     * @return void
     */
    public function testCanAssertTestConditionsWereMet(): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class);

        // this will intentionally always pass and there are no assertions to be made
        $instance->assertConditionsMet('test');
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::expectOutputString()
     * @dataProvider providerExpectOutputString
     *
     * @param bool $expectException
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanExpectOutputString(bool $expectException): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class);

        if ($expectException) {
            $property = new ReflectionProperty($instance, '__contentFilterCallback');
            $property->setAccessible(true);
            $property->setValue($instance, function () {
                return false;
            });

            $this->expectException(InvalidArgumentException::class);
        }

        $instance->expectOutputString('test');

        // parent method will not run in the context of this test, this will prevent method flagging no assertions performed
        $instance->assertConditionsMet();
    }

    /** @see testCanExpectOutputString */
    public function providerExpectOutputString(): Generator
    {
        yield 'Should not throw an exception' => [false];
        yield 'Should throw an exception' => [true];
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::assertEqualsHtml()
     *
     * @return void
     * @throws Exception
     */
    public function testCanAssertEqualsHtml(): void
    {
        $instance = $this->getMockForAbstractClass(TestCase::class);

        $instance->assertEqualsHtml('<p>test</p>', "<p>test</p>");

        $this->expectException(ExpectationFailedException::class);

        $instance->assertEqualsHtml('<p>foo</p>', '<p>bar</p>');
    }

    /**
     * @covers \WP_Mock\Tools\TestCase::mockStaticMethod()
     * @dataProvider providerMockStaticMethod
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param bool $usingPatchwork
     * @param bool $invalidMethod
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanMockStaticMethod(bool $usingPatchwork, bool $invalidMethod): void
    {
        $wpMock = Mockery::mock('overload:WP_Mock');
        /** @phpstan-ignore-next-line */
        $wpMock->shouldReceive('usingPatchwork')->andReturns($usingPatchwork);

        $class = new class () {
            public static function testMethod(): bool
            {
                return true;
            }
        };

        $this->assertTrue($class::testMethod());

        $instance = $this->getMockForAbstractClass(TestCase::class);
        $method = new ReflectionMethod($instance, 'mockStaticMethod');
        $method->setAccessible(true);

        if (! $usingPatchwork || $invalidMethod) {
            $this->expectException(Exception::class);
        }

        /** @var Mockery\Expectation $mockExpectation */
        $mockExpectation = $method->invokeArgs($instance, [get_class($class), $invalidMethod ? 'invalid' : 'testMethod']);
        $mockExpectation->once()->andReturnFalse();

        $this->assertFalse($class::testMethod());
    }

    /** @see testCanMockStaticMethod */
    public function providerMockStaticMethod(): Generator
    {
        yield 'Patchwork is disabled' => [false, false];
        yield 'Referencing invalid method' => [true, true];
        yield 'Should mock method' => [true, false];
    }
}

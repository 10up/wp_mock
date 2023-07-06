<?php

namespace WP_Mock\Tests\Unit\WP_Mock;

use Exception;
use Generator;
use InvalidArgumentException;
use Mockery\CompositeExpectation;
use Mockery\CountValidator\Exact;
use Mockery\Expectation;
use Mockery\Mock;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_Mock\Functions;
use WP_Mock\Functions\Handler;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock\Functions
 */
final class FunctionsTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Functions::__construct()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanInitialize(): void
    {
        $functions = new Functions();

        $mockedFunctions = new ReflectionProperty($functions, 'mockedFunctions');
        $mockedFunctions->setAccessible(true);

        $this->assertSame([], $mockedFunctions->getValue($functions));

        $internalFunctions = new ReflectionProperty($functions, 'internalFunctions');
        $internalFunctions->setAccessible(true);

        $this->assertSame([], $internalFunctions->getValue($functions));

        $patchworkFunctions = new ReflectionProperty($functions, 'patchworkFunctions');
        $patchworkFunctions->setAccessible(true);

        $this->assertSame([], $patchworkFunctions->getValue($functions));

        $userMockedFunctions = new ReflectionProperty($functions, 'userMockedFunctions');
        $userMockedFunctions->setAccessible(true);

        $this->assertSame([
            '__',
            '_e',
            '_n',
            '_x',
            'add_action',
            'add_filter',
            'apply_filters',
            'do_action',
            'esc_attr',
            'esc_attr__',
            'esc_attr_e',
            'esc_attr_x',
            'esc_html',
            'esc_html__',
            'esc_html_e',
            'esc_html_x',
            'esc_js',
            'esc_textarea',
            'esc_url',
            'esc_url_raw',
        ], $userMockedFunctions->getValue($functions));

        $userMockedFunctions->setValue($functions, []);
        $functions->flush();
    }

    /**
     * @covers \WP_Mock\Functions::register()
     *
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     *
     * @return void
     * @throws Exception
     */
    public function testCanRegister(): void
    {
        $handler = new ReflectionProperty(Handler::class, 'handlers');
        $handler->setAccessible(true);

        $this->assertSame([], $handler->getValue());

        $expectation = new Expectation(\Mockery::mock('wp_api'), 'myWpFunction');
        $functions = $this->createPartialMock(Functions::class, [
            'generateFunction',
            'setUpMock'
        ]);

        $functions->expects($this->once())
            ->method('generateFunction')
            ->with('myWpFunction');

        $functions->expects($this->once())
            ->method('setUpMock')
            ->willReturn($expectation);

        $this->assertSame($expectation, $functions->register('myWpFunction'));

        $handlers = $handler->getValue();

        $this->assertIsArray($handlers);
        $this->assertArrayHasKey('myWpFunction', $handlers);
    }

    /**
     * @covers \WP_Mock\Functions::setUpMock()
     * @dataProvider providerCanSetUpMock
     *
     * @param array<string, mixed> $expectationArgs
     * @return void
     * @throws Exception
     */
    public function testCanSetupMock(array $expectationArgs): void
    {
        $functions = new Functions();
        $method = new ReflectionMethod($functions, 'setUpMock');
        $method->setAccessible(true);
        $mock = new Mock();

        /** @var CompositeExpectation $compositeExpectation */
        $compositeExpectation = $method->invokeArgs($functions, [$mock, 'myWpFunction', $expectationArgs]);
        $expectations = new ReflectionProperty($compositeExpectation, '_expectations');
        $expectations->setAccessible(true);
        /** @var Expectation $expectation */
        $expectation = current((array) $expectations->getValue($compositeExpectation));

        $expectedName = new ReflectionProperty($expectation, '_name');
        $expectedName->setAccessible(true);

        $this->assertSame('myWpFunction', $expectedName->getValue($expectation));

        $expectedCountValidators = new ReflectionProperty($expectation, '_countValidators');
        $expectedCountValidators->setAccessible(true);

        if (! empty($expectationArgs['times'])) {
            /** @var Exact $expectedTimes */
            $expectedTimes = current((array) $expectedCountValidators->getValue($expectation));
            $expectedLimit = new ReflectionProperty($expectedTimes, '_limit');
            $expectedLimit->setAccessible(true);

            $this->assertSame($expectationArgs['times'], current((array) $expectedLimit->getValue($expectedTimes)));
        }

        $expectedArgs = new ReflectionProperty($expectation, '_expectedArgs');
        $expectedArgs->setAccessible(true);

        if (! empty($expectationArgs['args'])) {
            $this->assertSame($expectationArgs['args'], $expectedArgs->getValue($expectation));
        }

        $expectedReturnQueue = new ReflectionProperty($expectation, '_returnQueue');
        $expectedReturnQueue->setAccessible(true);

        if (! empty($expectationArgs['return'])) {
            $this->assertSame($expectationArgs['return'], current((array) $expectedReturnQueue->getValue($expectation)));
        }
    }

    /** @see testCanSetupMock */
    public function providerCanSetUpMock(): Generator
    {
        yield 'With args' => [['args' => ['foo', 'bar']]];
        yield 'With return value' => [['return' => 'foo']];
        yield 'With times' => [['times' => 2]];
    }

    /**
     * @covers \WP_Mock\Functions::generateFunction()
     * @dataProvider providerCanGenerateFunction
     *
     * @param bool $willCreate
     * @param bool $willReplace
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanGenerateFunction(bool $willCreate, bool $willReplace): void
    {
        $functionName = 'myFunction';

        $functions = $this->getMockBuilder(Functions::class)
            ->onlyMethods(['sanitizeFunctionName', 'validateFunctionName', 'createFunction', 'replaceFunction'])
            ->getMock();

        $functions->expects($this->once())
            ->method('sanitizeFunctionName')
            ->with($functionName)
            ->willReturnArgument(0);

        $functions->expects($this->once())
            ->method('validateFunctionName')
            ->with($functionName);

        $functions->expects($this->once())
            ->method('createFunction')
            ->with($functionName)
            ->willReturn($willCreate);

        $functions->expects(! $willCreate ? $this->once() : $this->never())
            ->method('replaceFunction')
            ->with($functionName)
            ->willReturn($willReplace);

        $method = new ReflectionMethod($functions, 'generateFunction');
        $method->setAccessible(true);
        $method->invokeArgs($functions, [$functionName]);
    }

    /** @see testCanGenerateFunction */
    public function providerCanGenerateFunction(): Generator
    {
        yield 'Function is created' => [true, false];
        yield 'Function is replaced' => [false, true];
        yield 'Function is not created or replaced' => [false, false];
    }

    /**
     * @covers \WP_Mock\Functions::createFunction()
     * @dataProvider providerCanCreateFunction
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param string $functionName
     * @param string[] $functionsList
     * @param bool $functionWillExist
     * @param bool $functionWillBeRegistered
     * @param bool $expectedReturnValue
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanCreateFunction(string $functionName, array $functionsList, bool $functionWillExist, bool $functionWillBeRegistered, bool $expectedReturnValue): void
    {
        $functions = new Functions();

        $userMockedFunctions = new ReflectionProperty($functions, 'userMockedFunctions');
        $userMockedFunctions->setAccessible(true);
        $userMockedFunctions->setValue($functions, $functionsList);

        $createFunction = new ReflectionMethod($functions, 'createFunction');
        $createFunction->setAccessible(true);

        $this->assertSame($expectedReturnValue, $createFunction->invokeArgs($functions, [$functionName]));
        $this->assertSame($functionWillExist, function_exists($functionName));

        if ($functionWillBeRegistered) {
            $this->assertContains($functionName, (array) $userMockedFunctions->getValue($functions));
        }

        $userMockedFunctions->setValue($functions, []);
        $functions->flush();
    }

    /** @see testCanCreateFunction */
    public function providerCanCreateFunction(): Generator
    {
        yield 'Function is already registered' => ['myWpMockFunction', ['myWpMockFunction'], false, true, true];
        yield 'Function already exists' => ['str_replace', [], true, false, false];
        yield 'Function should be created' => ['myWpMockFunction', [], true, true, true];
    }

    /**
     * @covers \WP_Mock\Functions::replaceFunction()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanReplaceFunction(): void
    {
        $functions = new Functions();

        $property = new ReflectionProperty($functions, 'patchworkFunctions');
        $property->setAccessible(true);
        $this->assertSame([], $property->getValue($functions));

        $method = new ReflectionMethod($functions, 'replaceFunction');
        $method->setAccessible(true);

        $this->assertTrue($method->invokeArgs($functions, ['myWpMockFunction']));
        $this->assertSame(['myWpMockFunction'], $property->getValue($functions));
    }

    /**
     * @covers \WP_Mock\Functions::sanitizeFunctionName()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanSanitizeFunctionName(): void
    {
        $functions = new Functions();
        $method = new ReflectionMethod($functions, 'sanitizeFunctionName');
        $method->setAccessible(true);

        $this->assertSame('Name\Space\myFunction', $method->invokeArgs($functions, ['Name\\Space\\myFunction']));
        $this->assertSame('myFunction', $method->invokeArgs($functions, ['\\myFunction']));
        $this->assertSame('myFunction', $method->invokeArgs($functions, ['myFunction']));
    }

    /**
     * @covers \WP_Mock\Functions::validateFunctionName()
     * @dataProvider providerCanValidateFunction
     *
     * @param string $functionName
     * @param bool $validates
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanValidateFunction(string $functionName, bool $validates): void
    {
        if (! $validates) {
            $this->expectException(InvalidArgumentException::class);
        }

        $functions = new Functions();
        $method = new ReflectionMethod($functions, 'validateFunctionName');
        $method->setAccessible(true);
        $method->invokeArgs($functions, [$functionName]);

        $this->assertConditionsMet();
    }

    /** @see testCanValidateFunction */
    public function providerCanValidateFunction(): Generator
    {
        yield 'Invalid function name' => ['#!?', false];
        yield 'Internal PHP function' => ['str_replace', false];
        yield 'Reserved function name' => ['callable', false];
        yield 'Valid function name' => ['my_function', true];
    }

    /**
     * @covers \WP_Mock\Functions::anyOf()
     * @dataProvider providerMatchAnyTypes
     *
     * @param bool $expected
     * @param mixed $matchedValue
     * @param mixed...$typesToMatch
     * @return void
     * @throws Exception
     */
    public function testCanSetUpArgumentPlaceholderOfAnyType(bool $expected, $matchedValue, $typesToMatch): void
    {
        $anyType = Functions::anyOf($typesToMatch);

        $this->assertSame($expected, $anyType->match($matchedValue));
    }

    /** @see testCanSetUpArgumentPlaceholderOfAnyType */
    public function providerMatchAnyTypes(): Generator
    {
        yield 'Match expected string' => [true, 'string', 'string'];
        yield 'Does not match expected string' => [false, 'string', 123];
        yield 'Match expected number' => [true, 1, 1];
        yield 'Does not match expected number' => [false, 1, 2];
        yield 'Match expected array' => [true, ['foo', 'bar'], ['foo', 'bar']];
        yield 'Does not match expected array' => [false, ['baz'], ['foo', 'bar']];
    }

    /**
     * @covers \WP_Mock\Functions::type()
     * @dataProvider providerMatchTypes
     *
     * @param string $typeToMatch
     * @param mixed $matchedValue
     * @return void
     * @throws Exception
     */
    public function testCanSetUpArgumentPlaceholderOfStrictType(string $typeToMatch, $matchedValue): void
    {
        $type = Functions::type($typeToMatch);

        $this->assertTrue($type->match($matchedValue));
    }

    /** @see testCanSetUpArgumentPlaceholderOfType */
    public function providerMatchTypes(): Generator
    {
        yield ['string', 'string'];
        yield ['integer', 1];
        yield ['double', 1.1];
        yield ['bool', true];
        yield ['array', []];
        yield ['null', null];
    }
}

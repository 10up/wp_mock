<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Tools\Constraints;

use Exception;
use Generator;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Tools\Constraints\IsEqualHtml;

/**
 * @covers \WP_Mock\Tools\Constraints\IsEqualHtml
 */
final class IsEqualHtmlTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Tools\Constraints\IsEqualHtml::__construct()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testConstructor(): void
    {
        $props = [
            'value' => 'Test',
            'delta' => 1.2,
            'canonicalize' => true,
            'ignoreCase' => true,
        ];

        $constraint = new IsEqualHtml($props['value'], $props['delta'], $props['canonicalize'], $props['ignoreCase']);

        foreach ($props as $key => $value) {
            $property = new ReflectionProperty($constraint, $key);
            $property->setAccessible(true);

            $this->assertSame($value, $property->getValue($constraint));
        }
    }

    /**
     * @covers \WP_Mock\Tools\Constraints\IsEqualHtml::clean()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanClean(): void
    {
        $value = "\n\t <p>Test </p>\r";
        $constraint = new IsEqualHtml($value);
        $method = new ReflectionMethod($constraint, 'clean');
        $method->setAccessible(true);

        $this->assertSame('<p>Test </p>', $method->invokeArgs($constraint, [$value]));
    }

    /**
     * @covers \WP_Mock\Tools\Constraints\IsEqualHtml::evaluate()
     * @dataProvider providerEvaluate
     *
     * @param string $value
     * @param string $otherValue
     * @param bool $returnResult
     * @param bool|null $expected
     * @param bool|null $throwsException
     * @return void
     * @throws Exception
     */
    public function testCanEvaluate(
        string $value,
        string $otherValue,
        bool $returnResult,
        ?bool $expected,
        ?bool $throwsException = null
    ): void {
        $constraint = new IsEqualHtml($value);

        if ($throwsException) {
            $this->expectException(ExpectationFailedException::class);
        }

        $this->assertSame($expected, $constraint->evaluate($otherValue, 'Test error message', $returnResult));
    }

    /** @see testCanEvaluate */
    public function providerEvaluate(): Generator
    {
        yield 'The two HTML strings are the same (return bool)' => [
            'value' => '<strong>Test</strong>',
            'otherValue' => '<strong>Test</strong>',
            'returnResult' => true,
            'expected' => true,
        ];

        yield 'The two HTML strings are the same (throw exception)' => [
            'value' => '<strong>Test</strong>',
            'otherValue' => '<strong>Test</strong>',
            'returnResult' => false,
            'expected' => null,
            'throwsException' => false,
        ];

        yield 'The two HTML strings are not the same (return bool)' => [
            'value' => '<strong>Test</strong>',
            'otherValue' => '<em>Test</em>',
            'returnResult' => true,
            'expected' => false,
        ];

        yield 'The two HTML strings are not the same (throw exception)' => [
            'value' => '<strong>Test</strong>',
            'otherValue' => '<em>Test</em>',
            'returnResult' => false,
            'expected' => null,
            'throwsException' => true,
        ];
    }

    /**
     * @covers \WP_Mock\Tools\Constraints\IsEqualHtml::toString()
     *
     * @return void
     * @throws Exception
     */
    public function testCanConvertToString(): void
    {
        $constraint = new IsEqualHtml('<body>Test</body>');

        $this->assertSame('html is equal to \'<body>Test</body>\'', $constraint->toString());
    }
}

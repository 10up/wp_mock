<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Tools\Constraints;

use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Tools\Constraints\IsEqualHtml;

/**
 * @covers \WP_Mock\Tools\Constraints\IsEqualHtml
 */
#[CoversClass(IsEqualHtml::class)]
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
        $constraint = new IsEqualHtml('Test');

        $property = new ReflectionProperty($constraint, 'value');
        $property->setAccessible(true);

        $this->assertSame('Test', $property->getValue($constraint));
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
     * @covers \WP_Mock\Tools\Constraints\IsEqualHtml::matches()
     * @dataProvider providerMatches
     *
     * @param string $value
     * @param string $otherValue
     * @param bool $expected
     * @return void
     * @throws Exception
     */
    #[DataProvider('providerMatches')]
    public function testCanMatch(string $value, string $otherValue, bool $expected): void
    {
        $constraint = new IsEqualHtml($value);

        $this->assertSame($expected, $constraint->matches($otherValue));
    }

    /** @see testCanMatch */
    public static function providerMatches(): Generator
    {
        yield 'identical HTML' => [
            'value' => '<strong>Test</strong>',
            'otherValue' => '<strong>Test</strong>',
            'expected' => true,
        ];

        yield 'whitespace-insensitive match' => [
            'value' => "<strong>\n\t Test</strong>",
            'otherValue' => '<strong>Test</strong>',
            'expected' => true,
        ];

        yield 'different HTML' => [
            'value' => '<strong>Test</strong>',
            'otherValue' => '<em>Test</em>',
            'expected' => false,
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

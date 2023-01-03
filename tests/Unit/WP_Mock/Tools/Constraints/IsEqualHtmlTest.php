<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Tools\Constraints;

use Exception;
use Generator;
use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Tools\Constraints\IsEqualHtml;

/**
 * @covers \WP_Mock\Tools\Constraints\IsEqualHtml
 */
final class IsEqualHtmlTest extends WP_MockTestCase
{
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
    ) : void {
        $constraint = new IsEqualHtml($value);

        if ($throwsException) {
            $this->expectException(ExpectationFailedException::class);
        }

        $this->assertSame($expected, $constraint->evaluate($otherValue, 'Test error message', $returnResult));
    }

    /** @see testCanEvaluate */
    public function providerEvaluate() : Generator
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
}

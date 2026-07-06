<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Tools\Constraints;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Tools\Constraints\ExpectationsMet;

#[CoversClass(ExpectationsMet::class)]
final class ExpectationsMetTest extends WP_MockTestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testMatches(): void
    {
        $constraint = new ExpectationsMet();

        $this->assertTrue($constraint->matches(null));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCanConvertToString(): void
    {
        $this->assertSame('WP_Mock expectations are met', (new ExpectationsMet())->toString());
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanGetFailureDescription(): void
    {
        $constraint = new ExpectationsMet();
        $method = new ReflectionMethod($constraint, 'failureDescription');
        $method->setAccessible(true);

        $this->assertSame('WP_Mock expectations are met', $method->invokeArgs($constraint, [null]));
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanGetAdditionalFailureDescription(): void
    {
        $constraint = new ExpectationsMet();
        $method = new ReflectionMethod($constraint, 'additionalFailureDescription');
        $method->setAccessible(true);
        $property = new ReflectionProperty($constraint, 'failureDescription');
        $property->setAccessible(true);

        $property->setValue($constraint, "\n\nTest\r");

        $this->assertSame('Test', $method->invokeArgs($constraint, [null]));
    }
}

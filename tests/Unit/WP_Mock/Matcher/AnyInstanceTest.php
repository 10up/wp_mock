<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Matcher;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionException;
use WP_Mock\Matcher\AnyInstance;
use WP_Mock\Tests\Mocks\SampleClass;
use WP_Mock\Tests\Mocks\SampleSubClass;
use WP_Mock\Tests\WP_MockTestCase;

#[CoversClass(AnyInstance::class)]
class AnyInstanceTest extends WP_MockTestCase
{
    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testExactClassInstanceMatchesTrue(): void
    {
        $sut = new AnyInstance(new SampleClass());

        $exactClassAction = new SampleClass();

        $result = $sut->match($exactClassAction);

        $this->assertTrue($result);
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testExactClassStringMatchesTrue(): void
    {
        $sut = new AnyInstance(SampleClass::class);

        $exactClassAction = new SampleClass();

        $result = $sut->match($exactClassAction);

        $this->assertTrue($result);
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testSubClassMatchesTrue(): void
    {
        $sut = new AnyInstance(SampleClass::class);

        $subClassAction = new SampleSubClass();

        $result = $sut->match($subClassAction);

        $this->assertTrue($result);
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testWrongClassMatchesFalse(): void
    {
        $sut = new AnyInstance(SampleClass::class);

        $wrongClassAction = new \stdClass();

        $result = $sut->match($wrongClassAction);

        $this->assertFalse($result);
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testClosureMatchesFalse(): void
    {
        $sut = new AnyInstance(SampleClass::class);

        $closureAction = function () {
        };

        $result = $sut->match($closureAction);

        $this->assertFalse($result);
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testStringFunctionMatchesFalse(): void
    {
        $sut = new AnyInstance(SampleClass::class);

        $stringFunctionAction = 'action_name';

        $result = $sut->match($stringFunctionAction);

        $this->assertFalse($result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testToString(): void
    {
        $sut = new AnyInstance(SampleClass::class);

        $result = "$sut";

        $this->assertEquals("<AnyInstance[WP_Mock\Tests\Mocks\SampleClass]>", $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCannotConstructWithoutObject(): void
    {
        $this->expectException(Exception::class);

        new AnyInstance('NotAClass');
    }
}

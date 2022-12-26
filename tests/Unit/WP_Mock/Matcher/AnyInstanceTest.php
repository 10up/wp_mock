<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Matcher;

use Exception;
use ReflectionException;
use WP_Mock\Matcher\AnyInstance;
use WP_Mock\Tests\Mocks\SampleClass;
use WP_Mock\Tests\Mocks\SampleSubClass;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock\Matcher\AnyInstance
 */
class AnyInstanceTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Matcher\AnyInstance::match()
     *
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
     * @covers \WP_Mock\Matcher\AnyInstance::match()
     *
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
     * @covers \WP_Mock\Matcher\AnyInstance::match()
     *
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
     * @covers \WP_Mock\Matcher\AnyInstance::match()
     *
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
     * @covers \WP_Mock\Matcher\AnyInstance::match()
     *
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
     * @covers \WP_Mock\Matcher\AnyInstance::match()
     *
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
     * @covers \WP_Mock\Matcher\AnyInstance::__toString()
     *
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
     * @covers \WP_Mock\Matcher\AnyInstance::__construct()
     *
     * @return void
     * @throws Exception
     */
    public function testCannotConstructWithoutObject(): void
    {
        $this->expectException(Exception::class);

        new AnyInstance('NotAClass');
    }
}

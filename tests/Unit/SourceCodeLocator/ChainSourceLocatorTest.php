<?php

namespace Phpactor\WorseReflection\Tests\Unit\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\SourceCodeLocator;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\SourceCodeLocator\ChainSourceLocator;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\Exception\SourceNotFound;

class ChainSourceLocatorTest extends TestCase
{
    private $locator1;
    private $locator2;
    private $chain;

    public function setUp()
    {
        $this->locator1 = $this->prophesize(SourceCodeLocator::class);
        $this->locator2 = $this->prophesize(SourceCodeLocator::class);
    }

    /**
     * @testdox It throws an exception if no loaders found.
     * @expectedException Phpactor\WorseReflection\Exception\SourceNotFound
     */
    public function testNoLocators()
    {
        $this->locate([], ClassName::fromString('as'));
    }

    /**
     * @testdox It delegates to first loader.
     */
    public function testDelegateToFirst()
    {
        $expectedSource = SourceCode::fromString('hello');
        $class = ClassName::fromString('Foobar');
        $this->locator1->locate($class)->willReturn($expectedSource);
        $this->locator2->locate($class)->shouldNotBeCalled();

        $source = $this->locate([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ], $class);

        $this->assertSame($expectedSource, $source);
    }

    /**
     * @testdox It delegates to second if first throws exception.
     */
    public function testDelegateToSecond()
    {
        $expectedSource = SourceCode::fromString('hello');
        $class = ClassName::fromString('Foobar');
        $this->locator1->locate($class)->willThrow(new SourceNotFound('Foo'));
        $this->locator2->locate($class)->willReturn($expectedSource);

        $source = $this->locate([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ], $class);

        $this->assertSame($expectedSource, $source);
    }

    /**
     * @testdox It throws an exception if all fail
     * @expectedException Phpactor\WorseReflection\Exception\SourceNotFound
     * @expectedExceptionMessage Could not find source with "Foobar"
     */
    public function testAllFail()
    {
        $expectedSource = SourceCode::fromString('hello');
        $class = ClassName::fromString('Foobar');
        $this->locator1->locate($class)->willThrow(new SourceNotFound('Foo'));
        $this->locator2->locate($class)->willThrow(new SourceNotFound('Foo'));

        $this->locate([
            $this->locator1->reveal(),
            $this->locator2->reveal()
        ], $class);
    }

    private function locate(array $locators, ClassName $className)
    {
        $locator = new ChainSourceLocator($locators);
        return $locator->locate($className);
    }
}

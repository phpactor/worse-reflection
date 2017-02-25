<?php

namespace DTL\WorseReflection\Tests\Unit\SourceLocator;

use DTL\WorseReflection\Source;
use DTL\WorseReflection\SourceLocator\Exception\SourceNotFoundException;
use DTL\WorseReflection\SourceLocator\AggregateSourceLocator;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\SourceLocator;

class AggregateSourceLocatorTest extends \PHPUnit_Framework_TestCase
{
    private $innerLocator1;
    private $innerLocator2;
    private $locator;

    public function setUp()
    {
        $this->innerLocator1 = $this->prophesize(SourceLocator::class);
        $this->innerLocator2 = $this->prophesize(SourceLocator::class);
        $this->source = $this->prophesize(Source::class);

        $this->locator = new AggregateSourceLocator([
            $this->innerLocator1->reveal(),
            $this->innerLocator2->reveal(),
        ]);
    }

    public function testLocateAndFind()
    {
        $className = ClassName::fromString('Foobar');
        $this->innerLocator1->locate($className)->willThrow(new SourceNotFoundException('Foobar'));
        $this->innerLocator2->locate($className)->willReturn($this->source->reveal());

        $source = $this->locator->locate($className);

        $this->assertSame($source, $this->source->reveal());
    }

    /**
     * @expectedException DTL\WorseReflection\SourceLocator\Exception\SourceNotFoundException
     * @expectedExceptionMessage Could not locate source using 2 locators
     */
    public function testLocateNotFound()
    {
        $className = ClassName::fromString('Foobar');
        $this->innerLocator1->locate($className)->willThrow(new SourceNotFoundException('Foobar'));
        $this->innerLocator2->locate($className)->willThrow(new SourceNotFoundException('Foobar'));

        $source = $this->locator->locate($className);

        $this->assertSame($source, $this->source->reveal());
    }

}

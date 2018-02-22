<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

class TemporarySourceLocatorTest extends TestCase
{
    /**
     * @var TemporarySourceLocator
     */
    private $locator;

    public function setUp()
    {
        $this->locator = new TemporarySourceLocator();
    }

    public function testLocate()
    {
        $this->locator->setSourceCode(SourceCode::fromString('Hello'));
        $source = $this->locator->locate(ClassName::fromString('Foobar'));

        $this->assertEquals('Hello', (string) $source);
    }

    public function testThrowsExceptionWhenNoSourceSet()
    {
        $this->expectException(SourceNotFound::class);
        $this->locator->locate(ClassName::fromString('Foobar'));
    }

    public function testItCanClearThePreviouslySetSourceCode()
    {
        $this->expectException(SourceNotFound::class);

        $this->locator->setSourceCode(SourceCode::fromString('Hello'));
        $this->locator->clear();
        $this->locator->locate(ClassName::fromString('Foobar'));
    }
}

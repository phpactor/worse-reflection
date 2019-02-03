<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\SourceCodeLocator;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;

class TemporarySourceLocatorTest extends TestCase
{
    /**
     * @var TemporarySourceLocator
     */
    private $locator;

    public function setUp()
    {
        $this->reflector = $this->prophesize(SourceCodeReflector::class);
        $this->locator = new TemporarySourceLocator(
            $this->reflector->reveal()
        );

        $this->classCollection = $this->prophesize(ReflectionClassCollection::class);
    }

    public function testThrowsExceptionWhenNoSourceSet()
    {
        $this->expectException(SourceNotFound::class);
        $this->locator->locate(ClassName::fromString('Foobar'));
    }

    public function testThrowsExceptionWhenClassNotFound()
    {
        $source = SourceCode::fromString('<?php class Boobar {}');
        $this->expectException(SourceNotFound::class);
        $this->expectExceptionMessage('Class "Foobar" not found');

        $this->reflector->reflectClassesIn($source)->willReturn(
            $this->classCollection->reveal()
        );
        $this->classCollection->has('Foobar')->willReturn(false);

        $this->locator->pushSourceCode($source);

        $this->locator->locate(ClassName::fromString('Foobar'));
    }

    public function testReturnsSourceIfClassIsInTheSource()
    {
        $code = 'class Foobar {}';

        $this->reflector->reflectClassesIn($code)->willReturn(
            $this->classCollection->reveal()
        );
        $this->classCollection->has('Foobar')->willReturn(true);

        $this->locator->pushSourceCode(SourceCode::fromString($code));
        $source = $this->locator->locate(ClassName::fromString('Foobar'));
        $this->assertEquals($code, (string) $source);
    }
}

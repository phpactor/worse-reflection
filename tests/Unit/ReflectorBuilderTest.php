<?php

namespace Phpactor\WorseReflection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Psr\Log\LoggerInterface;

class ReflectorBuilderTest extends TestCase
{
    public function testBuildWithDefaults()
    {
        $reflector = ReflectorBuilder::create()->build();
        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testReplacesLogger()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $reflector = ReflectorBuilder::create()
            ->withLogger($logger->reveal())
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testHasOneLocator()
    {
        $locator = $this->prophesize(SourceCodeLocator::class);
        $reflector = ReflectorBuilder::create()
            ->addLocator($locator->reveal())
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testHasManyLocators()
    {
        $locator = $this->prophesize(SourceCodeLocator::class);
        $reflector = ReflectorBuilder::create()
            ->addLocator($locator->reveal())
            ->addLocator($locator->reveal())
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testWithSource()
    {
        $reflector = ReflectorBuilder::create()
            ->addSource('<?php class Foobar {}')
            ->build();

        $class = $reflector->reflectClass('Foobar');
        $this->assertEquals('Foobar', $class->name()->__toString());
        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testEnableCache()
    {
        $reflector = ReflectorBuilder::create()
            ->enableCache()
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }

    public function testEnableContextualSourceLocation()
    {
        $reflector = ReflectorBuilder::create()
            ->enableContextualSourceLocation()
            ->build();

        $this->assertInstanceOf(Reflector::class, $reflector);
    }
}

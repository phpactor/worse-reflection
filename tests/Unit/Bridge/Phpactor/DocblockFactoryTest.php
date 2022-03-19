<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockFactory as BridgeFactory;
use Phpactor\Docblock\DocblockFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\ReflectorBuilder;

class DocblockFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $innerFactory = new DocblockFactory();
        $factory = new BridgeFactory(ReflectorBuilder::create()->build(), $innerFactory);
        $docblock = $factory->create('/** @var asd */');
        $this->assertInstanceOf(DocBlock::class, $docblock);
    }
}

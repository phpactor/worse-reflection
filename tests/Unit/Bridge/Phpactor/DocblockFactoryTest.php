<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockFactory as BridgeFactory;
use Phpactor\Docblock\DocblockFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;

class DocblockFactoryTest extends TestCase
{
    public function testCreate()
    {
        $innerFactory = new DocblockFactory();
        $factory = new BridgeFactory($innerFactory);
        $docblock = $factory->create('/** @var asd */');
        $this->assertInstanceOf(DocBlock::class, $docblock);
    }
}

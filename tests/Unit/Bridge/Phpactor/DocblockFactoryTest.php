<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockFactory as BridgeFactory;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockFactory as PhpactorDocblockFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;

class DocblockFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $innerFactory = new PhpactorDocblockFactory();
        $factory = new BridgeFactory($innerFactory);
        $docblock = $factory->create('/** @var asd */');
        $this->assertInstanceOf(DocBlock::class, $docblock);
    }
}

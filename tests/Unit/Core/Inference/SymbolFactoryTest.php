<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

class SymbolFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var SymbolFactory
     */
    private $factory;

    /**
     * @var Node
     */
    private $node;


    public function setUp(): void
    {
        $this->factory = new SymbolFactory();
        $this->node = $this->prophesize(Node::class);
    }

    public function testInformationInvalidKeys(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid keys "asd"');
        $this->factory->context('hello', 10, 20, [ 'asd' => 'asd' ]);
    }

    public function testInformation(): void
    {
        $information = $this->factory->context('hello', 10, 20);
        $this->assertInstanceOf(SymbolContext::class, $information);
        $symbol = $information->symbol();

        $this->assertEquals('hello', $symbol->name());
        $this->assertEquals(10, $symbol->position()->start());
        $this->assertEquals(20, $symbol->position()->end());
    }

    public function testInformationOptions(): void
    {
        $containerType = Type::fromString('container');
        $type = Type::fromString('type');

        $information = $this->factory->context('hello', 10, 20, [
            'symbol_type' => Symbol::ARRAY,
            'container_type' => $containerType,
            'type' => $type,
            'value' => 1234
        ]);

        $this->assertInstanceOf(SymbolContext::class, $information);
        $this->assertSame($information->type(), $type);
        $this->assertSame($information->containerType(), $containerType);
        $this->assertEquals(1234, $information->value());
        $this->assertEquals(Symbol::ARRAY, $information->symbol()->symbolType());
    }
}

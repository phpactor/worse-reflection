<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\Expression\Variable;

class SymbolFactoryTest extends TestCase
{
    /**
     * @var SymbolFactory
     */
    private $factory;

    /**
     * @var Node
     */
    private $node;


    public function setUp()
    {
        $this->factory = new SymbolFactory();
        $this->node = $this->prophesize(Node::class);
    }

    /**
     * @testdox Creates Symbol information from node
     */
    public function testFromNode()
    {
        $this->node->getText()->willReturn('hello');
        $this->node->getStart()->willReturn(10);
        $this->node->getEndPosition()->willReturn(20);
        $information = $this->factory->information($this->node->reveal(), []);

        $this->assertEquals('hello', $information->symbol()->name());
        $this->assertEquals(10, $information->symbol()->position()->start());
        $this->assertEquals(20, $information->symbol()->position()->end());
    }

    /**
     * @testdox Creates Symbol information
     */
    public function testFromNodeAndToken()
    {
        $this->node->getText()->willReturn('hello');
        $this->node->getStart()->willReturn(10);
        $this->node->getEndPosition()->willReturn(20);
        $this->node->getFileContents()->willReturn('$foo->barbar');
        $token = new Token(1, 6, 6, 4);
        $information = $this->factory->information($this->node->reveal(), [
            'token' => $token,
        ]);

        $this->assertEquals('barb', $information->symbol()->name());
        $this->assertEquals(6, $information->symbol()->position()->start());
        $this->assertEquals(10, $information->symbol()->position()->end());
    }

    /**
     * @testdox Throws exception if non-token given as token
     */
    public function testFromNodeAndVariable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token');
        $variable = new Variable();
        $variable->name = '$hello';
        $this->factory->information($this->node->reveal(), [
            'token' => $variable,
        ]);
    }
}

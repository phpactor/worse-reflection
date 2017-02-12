<?php

namespace DTL\WorseReflection\Tests\Unit\Node;

use PhpParser\Node;
use DTL\WorseReflection\Node\LinearNodeTraverser;

class LinearNodeTraverserTest extends \PHPUnit_Framework_TestCase
{
    private $nodes;
    private $traverser;

    public function setUp()
    {
        $this->nodes = [
            new Node\Stmt\Class_('Foobar'),
            new Node\Stmt\ClassMethod('Barfoo'),
            new Node\Param('Param'),
            new Node\Expr\Variable('foobar')
        ];
        $this->traverser = new LinearNodeTraverser($this->nodes);
    }

    /**
     * It should return the top
     */
    public function testTop()
    {
        $this->assertSame(end($this->nodes), $this->traverser->top());
    }

    /**
     * It should seek back.
     */
    public function testSeekBack()
    {
        $this->assertEquals(
            new Node\Stmt\ClassMethod('Barfoo'),
            $this->traverser->seekBack(function ($node) {
                return $node instanceof Node\Stmt\ClassMethod;
            })
        );

    }

}

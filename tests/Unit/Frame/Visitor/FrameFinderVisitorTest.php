<?php

namespace DTL\WorseReflection\Tests\Unit\Evaluation;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\NodeDispatcher;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\Node;
use DTL\WorseReflection\Frame\Visitor\FrameFinderVisitor;

class FrameFinderVisitorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideFind
     */
    public function testFind(string $source, array $expectedVariables)
    {
        $visitor = $this->getVisitor($source);
        $frame = $visitor->getFrame();

        $actualVariables = [];
        foreach ($frame->all() as $name => $node) {
            $actualVariables[$name] = $node->getType();
        }

        $this->assertEquals($expectedVariables, $actualVariables);
    }

    public function provideFind()
    {
        return [
            [
                '$foobar = "hello";  _',
                [
                    'foobar' => 'Scalar_String',
                ],
            ],
            [
                '$foobar = "barfoo"; function hello() { $barfoo = "nar";  _ }',
                [
                    'barfoo' => 'Scalar_String',
                ],
            ],
            [
                '$foobar = "barfoo"; function hello() { $barfoo = "nar"; }; $hello = "wor_ld";',
                [
                    'foobar' => 'Scalar_String',
                    'hello' => 'Scalar_String',
                ],
            ],
            [
                '$foobar = "barfoo"; _ function hello() { $barfoo = "nar"; }; $hello = "world";',
                [
                    'foobar' => 'Scalar_String',
                ],
            ],
            [
                '$foobar = "barfoo"; function he_llo() { $barfoo = "nar"; }; $hello = "world";',
                [
                ],
            ],
            [
                'class Barfoo { public $foobar; public function barfoo() { $bar = _123; } }',
                [
                    'bar' => 'Scalar_LNumber',
                    'this' => 'Stmt_Class',
                ],
            ],
            [
                'class Barfoo { public $foobar; public function barfoo() { $bar = 123; if ($bar === $boo = 123) { $baz = 123;_ $bag = $baz; } ; } }',
                [
                    'bar' => 'Scalar_LNumber',
                    'this' => 'Stmt_Class',
                    'boo' => 'Scalar_LNumber',
                    'baz' => 'Scalar_LNumber',
                ],
            ],
            [
                'class Barfoo { public $foobar; public function barfoo() { $bar = 123; if ($bar === $boo = 123) { $baz = 123; $bag = $baz; _} ; } }',
                [
                    'bar' => 'Scalar_LNumber',
                    'this' => 'Stmt_Class',
                    'boo' => 'Scalar_LNumber',
                    'baz' => 'Scalar_LNumber',
                    'bag' => 'Scalar_LNumber',
                ],
            ],
            [

                <<<'EOT'
class BarBar
{
}

class Foobar
{
    public function hello()
    {
        $bar = $this->world();_
    }

    public function world(): Barbar
    {
    }
    }
EOT
                , [
                    'bar' => 'Expr_MethodCall',
                    'this' => 'Stmt_Class',
                ],
            ]
        ];
    }

    /**
     * It should return the node at the given offset.
     *
     * @dataProvider provideNodeAtOffset
     */
    public function testNodeAtOffset($source, $expectedType)
    {
        $visitor = $this->getVisitor($source);

        if (null === $expectedType) {
            $this->assertFalse($visitor->hasNodeAtOffset());
            return;
        }

        $this->assertTrue($visitor->hasNodeAtOffset());
        $nodeAtOffset = $visitor->getNodeAtOffset();
        $this->assertEquals($expectedType, $nodeAtOffset->top()->getType());
    }

    public function provideNodeAtOffset()
    {
        return [
            [
                '$fo_obar = 1;',
                'Expr_Variable',
            ],
            [
                '$foobar_ = 1;',
                'Expr_Assign',
            ],
            [
                '$foobar = _1;',
                'Scalar_LNumber',
            ],
            [
                '_  $foobar = _1;',
                null,
            ],
            [
                <<<'EOT'
class Class1
{
}

class Class2
{
    public function getClass1(): Class1
    {
    }

    public function execute()
    {
        $this->getClass1();_
    }
}
EOT
                , 'Stmt_ClassMethod',
                
            ],
        ];
    }

    private function getVisitor(string $source): FrameFinderVisitor
    {
        $parser = $this->getParser();
        list ($offset, $source) = $this->getOffsetAndSource($source);
        $stmts = $parser->parse($source->getSource());

        $visitor = new FrameFinderVisitor($offset);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);

        return $visitor;
    }
}

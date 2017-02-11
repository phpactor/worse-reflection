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
    private $finder;

    public function setUp()
    {
    }

    /**
     * @dataProvider provideEvaluate
     */
    public function testFind(string $source, array $expectedVariables)
    {
        $parser = $this->getParser();
        $offset = strpos($source, '_');

        if (false !== $offset) {
            $source = substr($source, 0, $offset) . substr($source, $offset + 1);
        }

        $stmts = $parser->parse('<?php ' . $source);

        $visitor = new FrameFinderVisitor($offset);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);

        $frame = $visitor->getFrame();

        $actualVariables = [];
        foreach ($frame->all() as $name => $node) {
            $actualVariables[$name] = $node->getType();
        }

        $this->assertEquals($expectedVariables, $actualVariables);
    }

    public function provideEvaluate()
    {
        return [
            [
                '$foobar_ = "hello";',
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
                'class Barfoo { public $foobar; public function barfoo() { $bar = 123; if ($bar === $boo = 123) { $baz = 123; _$bag = $baz; } ; } }',
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
        ];
    }
}

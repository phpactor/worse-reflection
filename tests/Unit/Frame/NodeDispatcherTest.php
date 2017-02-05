<?php

namespace DTL\WorseReflection\Tests\Unit\Evaluation;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\NodeDispatcher;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\Node;

class NodeDispatcherTest extends IntegrationTestCase
{
    private $nodeDispatcher;

    public function setUp()
    {
        $this->nodeDispatcher = new NodeDispatcher();
    }

    /**
     * @dataProvider provideEvaluate
     */
    public function testEvaluate(string $source, array $expectedVariables)
    {
        $parser = $this->getParser();
        $stmts = $parser->parse('<?php ' . $source);

        $visitor = new class extends NodeVisitorAbstract {
            public $nodeDispatcher;
            public $frame;

            public function enterNode(Node $node)
            {
                if ($node->getSubNodeNames()) {
                    $this->nodeDispatcher->__invoke($node, $this->frame);
                }
            }
        };
        $visitor->nodeDispatcher = $this->nodeDispatcher;
        $visitor->frame = $frame = new Frame();

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);

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
                '$foobar = "hello";',
                [
                    'foobar' => 'Scalar_String',
                ],
            ],
            [
                '$foobar = "hello"; $boofar = $foobar;',
                [
                    'foobar' => 'Scalar_String',
                    'boofar' => 'Scalar_String',
                ],
            ],
            [
                'function foobar($param1, $param2) {}',
                [
                    'param1' => 'Param',
                    'param2' => 'Param',
                ],
            ],
            [
                'class Foobar { function foobar($param1, $param2) {}}',
                [
                    'param1' => 'Param',
                    'param2' => 'Param',
                ],
            ],
            [
                '$foobar = $barfoo = $zoofoo = "foo";',
                [
                    'foobar' => 'Scalar_String',
                    'barfoo' => 'Scalar_String',
                    'zoofoo' => 'Scalar_String',
                ],
            ],
            [
                'list($foo, $bar) = [ "foo", 12 ];',
                [
                    'foo' => 'Scalar_String',
                    'bar' => 'Scalar_LNumber',
                ],
            ],
            [
                '$foo = "bar"; $bar = "foo"; unset($foo);',
                [
                    'bar' => 'Scalar_String',
                ],
            ],
        ];
    }
}

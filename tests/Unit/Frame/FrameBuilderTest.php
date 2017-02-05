<?php

namespace DTL\WorseReflection\Tests\Unit\Evaluation;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\FrameBuilder;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\Node;

class FrameBuilderTest extends IntegrationTestCase
{
    private $frameBuilder;

    public function setUp()
    {
        $this->frameBuilder = new FrameBuilder();
    }

    /**
     * @dataProvider provideEvaluate
     */
    public function testEvaluate(string $source, array $expectedVariables)
    {
        $parser = $this->getParser();
        $stmts = $parser->parse('<?php ' . $source);

        $frame = new Frame();
        $visitor = new class extends NodeVisitorAbstract {
            public $frameBuilder;
            public $frame;

            public function enterNode(Node $node)
            {
                $this->frameBuilder->__invoke($node, $this->frame);
            }
        };
        $visitor->frameBuilder = $this->frameBuilder;
        $visitor->frame = $frame;

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
        ];
    }
}

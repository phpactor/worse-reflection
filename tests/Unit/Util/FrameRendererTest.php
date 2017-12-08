<?php

namespace Phpactor\WorseReflection\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
use Phpactor\WorseReflection\Util\FrameRenderer;

class FrameRendererTest extends TestCase
{
    public function testRender()
    {
        $frame = new Frame('foobar');
        $frame->new('child1');

        $frame->locals()->add(Variable::fromSymbolInformation(SymbolInformation::none()));
        $frame->properties()->add(Variable::fromSymbolInformation(SymbolInformation::none()));
        $frame->problems()->add(SymbolInformation::none());

        $child = $frame->new('child2');
        $child = $frame->locals()->add(Variable::fromSymbolInformation(SymbolInformation::none()));
        $child = $frame->properties()->add(Variable::fromSymbolInformation(SymbolInformation::none()));
        $child = $frame->problems()->add(SymbolInformation::none());

        $renderer = new FrameRenderer();

        $expected = <<<'EOT'
v: <unknown>:0 <unknown> NULL
v: <unknown>:0 <unknown> NULL
p: <unknown>:0 <unknown> NULL
p: <unknown>:0 <unknown> NULL
x: 0:
x: 0:
f: child1

f: child2

EOT
        ;

        $this->assertEquals($expected, $renderer->render($frame));
    }
}

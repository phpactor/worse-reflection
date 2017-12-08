<?php

namespace Phpactor\WorseReflection\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Util\FrameRenderer;

class FrameRendererTest extends TestCase
{
    public function testRender()
    {
        $frame = new Frame('foobar');
        $frame->new('child1');

        $frame->locals()->add(Variable::fromSymbolContext(SymbolContext::none()));
        $frame->properties()->add(Variable::fromSymbolContext(SymbolContext::none()));
        $frame->problems()->add(SymbolContext::none());

        $child = $frame->new('child2');
        $child = $frame->locals()->add(Variable::fromSymbolContext(SymbolContext::none()));
        $child = $frame->properties()->add(Variable::fromSymbolContext(SymbolContext::none()));
        $child = $frame->problems()->add(SymbolContext::none());

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

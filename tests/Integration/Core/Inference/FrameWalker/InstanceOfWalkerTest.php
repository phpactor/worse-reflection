<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Generator;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\InstanceOfWalker;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Type;

class InstanceOfWalkerTest extends FrameWalkerTestCase
{
    public function createWalker()
    {
        return new InstanceOfWalker(new SymbolFactory());
    }

    public function provideWalk(): Generator
    {
        yield 'infers type from instanceof' => [
            <<<'EOT'
<?php

if ($foobar instanceof Foobar) {
    <>
}
EOT
        , function (Frame $frame) {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals('Foobar', (string) $frame->locals()->first()->symbolContext()->types()->best());
        }
        ];
    }
}

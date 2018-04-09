<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Type;
use Generator;

class AssertWalkerTest extends FrameWalkerTestCase
{
    public function provideWalk(): Generator
    {
        yield 'assert instanceof' => [
            <<<'EOT'
<?php

assert($foobar instanceof Foobar);
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals());
                $this->assertEquals('Foobar', (string) $frame->locals()->first()->symbolContext()->types()->best());
            }
        ];

        yield 'assert instanceof negative' => [
            <<<'EOT'
<?php

assert(!$foobar instanceof Foobar);
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertEquals(0, $frame->locals()->count());
            }
        ];
    }
}

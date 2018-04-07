<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Type;
use Generator;

class CatchWalkerTest extends FrameWalkerTestCase
{
    public function provideWalk(): Generator
    {
        yield 'Exceptions' => [
            <<<'EOT'
<?php
try {
} catch (\Exception $exception) {
        <>
}

EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$exception'));
                $exception = $frame->locals()->byName('$exception')->first();
                $this->assertEquals(Type::fromString('\Exception'), $exception->symbolContext()->type());
            }
        ];
    }

}

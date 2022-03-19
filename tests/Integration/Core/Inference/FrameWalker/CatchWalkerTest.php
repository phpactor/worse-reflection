<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;
use Phpactor\WorseReflection\Tests\Trait\TrinaryTestTrait;

class CatchWalkerTest extends FrameWalkerTestCase
{
    use TrinaryTestTrait;

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
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$exception'));
                $exception = $frame->locals()->byName('$exception')->first();
                self::assertTrinaryTrue(
                    TypeFactory::class('\Exception')->is(
                        $exception->symbolContext()->type()
                    )
                );
            }
        ];

        yield 'Catch type-hint union' => [
            <<<'EOT'
                <?php
                try {
                } catch (Foo | Bar $exception) {
                        <>
                }

                EOT
        ,
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$exception'));
                $exception = $frame->locals()->byName('$exception')->first();
                self::assertEquals('Foo|Bar', $exception->symbolContext()->types()->__toString());
            }
        ];
    }
}

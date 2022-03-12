<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
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
            function (Frame $frame): void {
                $this->assertCount(1, $frame->locals()->byName('$exception'));
                $exception = $frame->locals()->byName('$exception')->first();
                $this->assertEquals(TypeFactory::fromString('\Exception'), $exception->symbolContext()->type());
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
                $this->assertEquals(Types::fromTypes([
                    TypeFactory::fromString('Foo'),
                    TypeFactory::fromString('Bar'),
                ]), $exception->symbolContext()->types());
            }
        ];
    }
}

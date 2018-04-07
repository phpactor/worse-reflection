<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Generator;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\InstanceOfWalker;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

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

        yield 'removes type if return' => [
            <<<'EOT'
<?php

if ($foobar instanceof Foobar) {
    return;
}
<>
EOT
        , function (Frame $frame, int $offset) {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals(Type::unknown(), $frame->locals()->atIndex(1)->symbolContext()->types()->best());
        }
        ];

        yield 'removes type if exception' => [
            <<<'EOT'
<?php

if ($foobar instanceof Foobar) {
    throw new Exception("HAI!");
}
<>
EOT
        , function (Frame $frame, int $offset) {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals(Type::unknown(), $frame->locals()->atIndex(1)->symbolContext()->types()->best());
        }
        ];

        yield 'adds no type information if bang negated' => [
            <<<'EOT'
<?php

if (!$foobar instanceof Foobar) {
}
<>
EOT
        , function (Frame $frame, int $offset) {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals(Type::unknown(), $frame->locals()->atIndex(0)->symbolContext()->types()->best());
        }
    ];

        yield 'adds no type information if false negated' => [
            <<<'EOT'
<?php

if (false === $foobar instanceof Foobar) {
}
<>
EOT
        , function (Frame $frame, int $offset) {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals(Type::unknown(), $frame->locals()->atIndex(0)->symbolContext()->types()->best());
        }
        ];


        yield 'adds type information if negated and if statement terminates' => [
            <<<'EOT'
<?php

if (!$foobar instanceof Foobar) {

    return;
}
<>
EOT
        , function (Frame $frame, int $offset) {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals(Type::unknown(), $frame->locals()->atIndex(0)->symbolContext()->types()->best());
            $this->assertEquals('Foobar', (string) $frame->locals()->atIndex(1)->symbolContext()->types()->best());
        }
        ];

        yield 'has no type information if double negated and if statement terminates' => [
            <<<'EOT'
<?php

if (!!$foobar instanceof Foobar) {

    return;
}
<>
EOT
        , function (Frame $frame, int $offset) {
            $this->assertCount(2, $frame->locals());
            $this->assertEquals('Foobar', (string) $frame->locals()->atIndex(1)->symbolContext()->types()->best());
            $this->assertEquals(Type::unknown(), $frame->locals()->atIndex(0)->symbolContext()->types()->best());
        }
        ];

        yield 'will create a union type with or' => [
            <<<'EOT'
<?php

if ($foobar instanceof Foobar || $foobar instanceof Barfoo) {

}
<>
EOT
        , function (Frame $frame, int $offset) {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals(Types::fromTypes([ Type::fromString('Foobar'), Type::fromString('Barfoo') ]), $frame->locals()->atIndex(0)->symbolContext()->types());
        }
        ];

        yield 'will create a union type with and' => [
            <<<'EOT'
<?php

if ($foobar instanceof Foobar && $foobar instanceof Barfoo) {

}
<>
EOT
        , function (Frame $frame, int $offset) {
            $this->assertCount(1, $frame->locals());
            $this->assertEquals(Types::fromTypes([ Type::fromString('Foobar'), Type::fromString('Barfoo') ]), $frame->locals()->atIndex(0)->symbolContext()->types());
        }
        ];
    }
}

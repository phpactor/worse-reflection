<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalker;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Tests\Integration\Core\Inference\FrameWalkerTestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Generator;

class ForeachWalkerTest extends FrameWalkerTestCase
{
    public function provideWalk(): Generator
    {
        yield 'Assigns type to foreach item' => [
            <<<'EOT'
<?php
/** @var int[] $items */
$items = [1, 2, 3, 4];

foreach ($items as $item) {
<>
}
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(3, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('item'));
                $this->assertEquals('int', (string) $frame->locals()->byName('item')->first()->symbolContext()->types()->best());
            }
        ];

        yield 'yields array keys' => [
            <<<'EOT'
<?php
/** @var int[] $items */
$items = [ 'one' => 1, 'two' => 2 ];

foreach ($items as $key => $item) {
<>
}
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(4, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('key'));
                $this->assertEquals(Type::unknown(), $frame->locals()->byName('key')->first()->symbolContext()->types()->best());
            }
        ];


        yield 'Assigns fully qualfied type to foreach item' => [
            <<<'EOT'
<?php

namespace Foobar;

/** @var Barfoo[] $items */
$items = [];

foreach ($items as $item) {
<>
}
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(3, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('item'));
                $this->assertEquals('Foobar\\Barfoo', (string) $frame->locals()->byName('item')->first()->symbolContext()->types()->best());
            }
        ];

        yield 'Assigns fully qualfied type to foreach from collection' => [
            <<<'EOT'
<?php

namespace Foobar;

/** @var Collection<Item> $items */
$items = new Collection();

foreach ($items as $item) {
<>
}
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(3, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('item'));
                $this->assertEquals('Foobar\\Collection', (string) $frame->locals()->byName('items')->first()->symbolContext()->types()->best());
                $this->assertEquals('Foobar\\Item', (string) $frame->locals()->byName('item')->first()->symbolContext()->types()->best());
            }
        ];

        yield 'It returns type for a foreach member (with a docblock)' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello()
    {
        /** @var Foobar $foobar */
        foreach ($collection as $foobar) {
            $foobar->foobar();
            <>
        }
    }
}
EOT
        , function (Frame $frame) {
            $vars = $frame->locals()->byName('foobar');
            $this->assertCount(2, $vars);
            $symbolInformation = $vars->atIndex(1)->symbolContext();
            $this->assertEquals('Foobar', (string) $symbolInformation->type());
        }];
    }
}

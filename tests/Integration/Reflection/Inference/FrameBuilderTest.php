<?php

namespace Phpactor\WorseReflection\Tests\Integration\Reflection\Inference;

use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\Inference\Frame;

class FrameBuilderTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideForMethod
     */
    public function testForMethod(string $source, array $classAndMethod, \Closure $assertion)
    {
        list($className, $methodName) = $classAndMethod;
        $reflector = $this->createReflector($source);
        $method = $reflector->reflectClass(ClassName::fromString($className))->methods()->get($methodName);
        $frame = $method->frame();

        $assertion($frame);
    }

    public function provideForMethod()
    {
        return [
            'It returns this and self' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello()
    {
    }
}

EOT
            , [ 'Foobar\Barfoo\Foobar', 'hello' ], function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$this'));
                $this->assertCount(1, $frame->locals()->byName('self'));
                $this->assertEquals(Type::fromString('Foobar\Barfoo\Foobar'), $frame->locals()->byName('$this')->first()->value()->type());
            }],
            'It returns method arguments' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello(World $world)
    {
    }
}

EOT
            , [ 'Foobar\Barfoo\Foobar', 'hello' ], function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$this'));
                $this->assertCount(1, $frame->locals()->byName('self'));
                $this->assertEquals(Type::fromString('Foobar\Barfoo\Foobar'), $frame->locals()->byName('$this')->first()->value()->type());
            }],
            'It registers string assignments' => [
                <<<'EOT'
<?php

class Foobar
{
    public function hello()
    {
        $foobar = 'foobar';
    }
}

EOT
            , [ 'Foobar', 'hello' ], function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$foobar'));
                $value = $frame->locals()->byName('$foobar')->first()->value();
                $this->assertEquals('string', (string) $value->type());
                $this->assertEquals('foobar', (string) $value->value());
            }],
            'It returns types for reassigned variables' => [
                <<<'EOT'
<?php

class Foobar
{
    public function hello(World $world = 'test')
    {
        $foobar = $world;
    }
}

EOT
            , [ 'Foobar', 'hello' ], function (Frame $frame) {
                $vars = $frame->locals()->byName('$foobar');
                $this->assertCount(1, $vars);
                $value = $vars->first()->value();
                $this->assertEquals('World', (string) $value->type());
                $this->assertEquals('test', (string) $value->value());
            }],
        ];
    }

    private function abc()
    {
        return [
            'It returns type for $this' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello(World $world)
    {
        $this;
    }
}

EOT
                , 126, Type::fromString('Foobar\Barfoo\Foobar')
            ],
            'It returns type for a property' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;
use Things\Response;

class Foobar
{
    /**
     * @var \Hello\World
     */
    private $foobar;

    public function hello(Barfoo $world)
    {
        $this->foobar;
    }
}
EOT
                , 215, Type::fromString('Hello\World')
            ],
            'It returns type for a variable assigned to an access expression' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Type1
{
    public function type2(): Type2
    {
    }
}

class Foobar
{
    /**
     * @var Type1
     */
    private $foobar;

    public function hello(Barfoo $world)
    {
        $foobar = $this->foobar->type2();
        $foobar;
    }
}
EOT
                , 269, Type::fromString('Foobar\Barfoo\Type2')
            ],
            'It returns the FQN for self' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(Barfoo $barfoo)
    {
        self::foobar();
    }
}

EOT
                , 106, Type::fromString('Foobar\Barfoo\Foobar')
            ],
            'It returns type for a for each member (with a docblock)' => [
                <<<'EOT'
<?php

/** @var $foobar Foobar */
foreach ($collection as $foobar) {
    $foobar->foobar();
}
EOT
                , 75, Type::fromString('Foobar')
            ],
        ];

    }
}

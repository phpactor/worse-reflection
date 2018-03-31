<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\TestUtils\ExtractOffset;

class FrameBuilderTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideForMethod
     */
    public function testForMethod(string $source, array $classAndMethod, \Closure $assertion)
    {
        list($className, $methodName) = $classAndMethod;
        $reflector = $this->createReflector($source);
        $method = $reflector->reflectClassLike(ClassName::fromString($className))->methods()->get($methodName);
        $frame = $method->frame();

        $assertion($frame, $this->logger());
    }

    public function provideForMethod()
    {
        yield 'It returns this' => [
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
            $this->assertCount(1, $frame->locals()->byName('this'));
            $this->assertEquals(Type::fromString('Foobar\Barfoo\Foobar'), $frame->locals()->byName('this')->first()->symbolContext()->type());
            $this->assertEquals(Symbol::VARIABLE, $frame->locals()->byName('this')->first()->symbolContext()->symbol()->symbolType());
        }];

        yield 'It returns method arguments' => [
            <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;

class Foobar
{
    public function hello(World $world)
    {
    }
}

EOT
        , [ 'Foobar\Barfoo\Foobar', 'hello' ], function (Frame $frame) {
            $this->assertCount(1, $frame->locals()->byName('this'));
            $this->assertEquals(
                Type::fromString('Foobar\Barfoo\Foobar'),
                $frame->locals()->byName('this')->first()->symbolContext()->type()
            );
        }];

        yield 'It registers string assignments' => [
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
            $this->assertCount(1, $frame->locals()->byName('foobar'));
            $symbolInformation = $frame->locals()->byName('foobar')->first()->symbolContext();
            $this->assertEquals('string', (string) $symbolInformation->type());
            $this->assertEquals('foobar', (string) $symbolInformation->value());
        }];
        yield 'It returns types for reassigned variables' => [
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
            $vars = $frame->locals()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('World', (string) $symbolInformation->type());
            $this->assertEquals('test', (string) $symbolInformation->value());
        }];

        yield 'It returns type for $this' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello(World $world)
    {
    }
}

EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame) {
            $vars = $frame->locals()->byName('this');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('Foobar', (string) $symbolInformation->type());
        }];

        yield 'It tracks assigned properties' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello(Barfoo $world)
    {
        $this->foobar = 'foobar';
    }
}
EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame) {
            $vars = $frame->properties()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('string', (string) $symbolInformation->type());
            $this->assertEquals('foobar', (string) $symbolInformation->value());
        }];

        yield 'It tracks assigned array properties' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello()
    {
        $this->foobar[] = 'foobar';
    }
}
EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame) {
            $vars = $frame->properties()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('array', (string) $symbolInformation->type());
            $this->assertEquals('foobar', (string) $symbolInformation->value());
        }];

        yield 'It tracks assigned from variable' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello(Barfoo $world)
    {
        $foobar = 'foobar';
        $this->$foobar = 'foobar';
    }
}
EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame) {
            $vars = $frame->properties()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('string', (string) $symbolInformation->type());
            $this->assertEquals('foobar', (string) $symbolInformation->value());
        }];

        yield 'It returns type for a for each member (with a docblock)' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello()
    {
        /** @var $foobar Foobar */
        foreach ($collection as $foobar) {
            $foobar->foobar();
        }
    }
}
EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame) {
            $vars = $frame->locals()->byName('foobar');
            $this->assertCount(1, $vars);
            $symbolInformation = $vars->first()->symbolContext();
            $this->assertEquals('Foobar', (string) $symbolInformation->type());
        }];

        yield 'Redeclared variables' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello()
    {
        $foobar = new Foobar();
        $foobar = new \stdClass();
    }
}
EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame) {
            $vars = $frame->locals()->byName('$foobar');
            $this->assertCount(2, $vars);
            $this->assertEquals('Foobar', (string) $vars->first()->symbolContext()->type()->className());
            $this->assertEquals('stdClass', (string) $vars->last()->symbolContext()->type()->className());
        }];

        yield 'Tolerates missing tokens' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello()
    {
        $reflection = )>classReflector->reflect(TestCase::class);
    }
}
EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame, $logger) {
            $this->assertContains('Non-node class passed to resolveNode, got', (string) $frame->problems());
        }];
    }

    /**
     * @dataProvider provideBuildForNode
     */
    public function testBuildForNode(string $source, \Closure $assertion)
    {
        list($source, $offset) = ExtractOffset::fromSource($source);
        $reflector = $this->createReflector($source);
        $offset = $reflector->reflectOffset(SourceCode::fromString($source), Offset::fromInt($offset));
        $assertion($offset->frame());
    }

    public function provideBuildForNode()
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
                $this->assertEquals(Type::fromString('Exception'), $exception->symbolContext()->type());
            }
        ];

        yield 'Respects closure scope' => [
            <<<'EOT'
<?php
$foo = 'bar';

$hello = function () {
    $bar = 'foo';
    <>
};
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$bar'), 'Scoped variable exists');
                $this->assertCount(0, $frame->locals()->byName('$foo'), 'Parent scoped variable doesnt exist');
            }
        ];

        yield 'Injects closure parameters' => [
            <<<'EOT'
<?php
$foo = 'bar';

$hello = function (Foobar $foo) {
    <>
};
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$foo'));
                $variable = $frame->locals()->byName('$foo')->first();
                $this->assertEquals(Type::fromString('Foobar'), $variable->symbolContext()->type());
            }
        ];

        yield 'Injects imported closure parent scope variables' => [
            <<<'EOT'
<?php
$zed = 'zed';
$art = 'art';

$hello = function () use ($zed) {
    <>
};
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $zed = $frame->locals()->byName('$zed')->first();
                $this->assertEquals('string', (string) $zed->symbolContext()->type());
                $this->assertEquals(Symbol::VARIABLE, $zed->symbolContext()->symbol()->symbolType());
            }
        ];

        yield 'Injects variables with @var (non-standard)' => [
            <<<'EOT'
<?php
/** @var $zed string */
$zed;
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('string', (string) $frame->locals()->byName('$zed')->last()->symbolContext()->type());
            }
        ];

        yield 'Injects variables with @var (standard)' => [
            <<<'EOT'
<?php
/** @var string $zed */
$zed;
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('string', (string) $frame->locals()->byName('$zed')->last()->symbolContext()->type());
            }
        ];

        yield 'Injects variables with @var namespaced' => [
            <<<'EOT'
<?php
namespace Foo;
/** @var Bar $zed */
$zed;
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Foo\\Bar', (string) $frame->locals()->byName('$zed')->last()->symbolContext()->type());
            }
        ];

        yield 'Injects variables with @var namespaced and qualified name' => [
            <<<'EOT'
<?php
namespace Foo;
/** @var Bar\Baz $zed */
$zed;
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Foo\\Bar\\Baz', (string) $frame->locals()->byName('$zed')->last()->symbolContext()->type());
            }
        ];

        yield 'Injects variables with @var namespaced with fully qualified name' => [
            <<<'EOT'
<?php
namespace Foo;
/** @var \Bar\Baz $zed */
$zed;
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Bar\\Baz', (string) $frame->locals()->byName('$zed')->last()->symbolContext()->type());
            }
        ];

        yield 'Injects variables with @var with imported namespace' => [
            <<<'EOT'
<?php

use Foo\Bar\Zed;
/** @var Zed\Baz $zed */
$zed;
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('$zed'));
                $this->assertEquals('Foo\Bar\Zed\Baz', (string) $frame->locals()->byName('$zed')->last()->symbolContext()->type());
            }
        ];

        yield 'Handles array assignments' => [
            <<<'EOT'
<?php
$foo = [ 'foo' => 'bar' ];
$bar = $foo['foo'];
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(2, $frame->locals());
                $this->assertEquals('array', (string) $frame->locals()->first()->symbolContext()->type());
                $this->assertEquals(['foo' => 'bar'], $frame->locals()->first()->symbolContext()->value());
                $this->assertEquals('string', (string) $frame->locals()->last()->symbolContext()->type());
                $this->assertEquals('bar', (string) $frame->locals()->last()->symbolContext()->value());
            }
        ];

        yield 'Includes list assignments' => [
            <<<'EOT'
<?php
list($foo, $bar) = [ 'foo', 'bar' ];
<>
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(2, $frame->locals());
                $this->assertEquals('foo', $frame->locals()->first()->symbolContext()->value());
                $this->assertEquals('string', (string) $frame->locals()->first()->symbolContext()->type());
            }
        ];

        yield 'Understands foreach' => [
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
                $this->assertCount(2, $frame->locals());
                $this->assertCount(1, $frame->locals()->byName('item'));
                $this->assertEquals('int', (string) $frame->locals()->byName('item')->first()->symbolContext()->types()->best());
            }
        ];
    }
}

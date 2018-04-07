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

        yield 'It injects method argument with inferred types' => [
            <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;

class Foobar
{
    /**
     * @param World[] $worlds
     * @param string $many
     */
    public function hello(array $worlds, $many)
    {
    }
}

EOT
        , [ 'Foobar\Barfoo\Foobar', 'hello' ], function (Frame $frame) {
            $this->assertCount(1, $frame->locals()->byName('many'));
            $this->assertEquals('string', (string) $frame->locals()->byName('many')->first()->symbolContext()->types()->best());

            $this->assertCount(1, $frame->locals()->byName('worlds'));
            $this->assertEquals('Foobar\Barfoo\World[]', (string) $frame->locals()->byName('worlds')->first()->symbolContext()->types()->best());
            $this->assertEquals('Foobar\Barfoo\World', (string) $frame->locals()->byName('worlds')->first()->symbolContext()->types()->best()->arrayType());

        }];

        yield 'It returns type for a for each member (with a docblock)' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello()
    {
        /** @var Foobar $foobar */
        foreach ($collection as $foobar) {
            $foobar->foobar();
        }
    }
}
EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame) {
            $vars = $frame->locals()->byName('foobar');
            $this->assertCount(2, $vars);
            $symbolInformation = $vars->atIndex(1)->symbolContext();
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
                $this->assertEquals(Type::fromString('\Exception'), $exception->symbolContext()->type());
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

        yield 'From return type with docblock' => [
            <<<'EOT'
<?php

namespace Foobar;

use Foo\Lister;

interface Barfoo
{
    /**
     * @return Lister<Collection>
     */
    public static function bar(): List;
}

class Baz
{
    public function (Barfoo $barfoo)
    {
        $bar = $barfoo->bar();
        <>
    }
}
<>
}
EOT
        ,
            function (Frame $frame) {
                $this->assertCount(3, $frame->locals());
                $this->assertEquals('Foo\Lister<Foobar\Collection>', (string) $frame->locals()->byName('bar')->first()->symbolContext()->types()->best());
                $this->assertEquals('Foobar\Collection', (string) $frame->locals()->byName('bar')->first()->symbolContext()->types()->best()->arrayType());
            }
        ];
    }
}

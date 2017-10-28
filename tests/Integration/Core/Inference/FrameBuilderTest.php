<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Tests\Integration\Util\CodeHelper;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\SourceCode;

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
        return [
            'It returns this' => [
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
                $this->assertEquals(Type::fromString('Foobar\Barfoo\Foobar'), $frame->locals()->byName('this')->first()->symbolInformation()->type());
                $this->assertEquals(Symbol::VARIABLE, $frame->locals()->byName('this')->first()->symbolInformation()->symbol()->symbolType());
            }],
            'It returns method arguments' => [
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
                    $frame->locals()->byName('this')->first()->symbolInformation()->type()
                );
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
                $this->assertCount(1, $frame->locals()->byName('foobar'));
                $symbolInformation = $frame->locals()->byName('foobar')->first()->symbolInformation();
                $this->assertEquals('string', (string) $symbolInformation->type());
                $this->assertEquals('foobar', (string) $symbolInformation->value());
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
                $vars = $frame->locals()->byName('foobar');
                $this->assertCount(1, $vars);
                $symbolInformation = $vars->first()->symbolInformation();
                $this->assertEquals('World', (string) $symbolInformation->type());
                $this->assertEquals('test', (string) $symbolInformation->value());
            }],
            'It returns type for $this' => [
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
                $symbolInformation = $vars->first()->symbolInformation();
                $this->assertEquals('Foobar', (string) $symbolInformation->type());
            }],
            'It tracks assigned properties' => [
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
                $symbolInformation = $vars->first()->symbolInformation();
                $this->assertEquals('string', (string) $symbolInformation->type());
                $this->assertEquals('foobar', (string) $symbolInformation->value());
            }],
            'It tracks assigned from variable' => [
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
                $symbolInformation = $vars->first()->symbolInformation();
                $this->assertEquals('string', (string) $symbolInformation->type());
                $this->assertEquals('foobar', (string) $symbolInformation->value());
            }],
            'It returns type for a for each member (with a docblock)' => [
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
                $symbolInformation = $vars->first()->symbolInformation();
                $this->assertEquals('Foobar', (string) $symbolInformation->type());
            }],
            'Redeclared variables' => [
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
                $this->assertEquals('Foobar', (string) $vars->first()->symbolInformation()->type()->className());
                $this->assertEquals('stdClass', (string) $vars->last()->symbolInformation()->type()->className());
            }],
            'Tolerates missing tokens' => [
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
                $this->assertContains('Non-node class passed to resolveNode, got', implode('', $logger->messages()));
            }],
        ];
    }

    /**
     * @dataProvider provideBuildForNode
     */
    public function testBuildForNode(string $source, \Closure $assertion)
    {
        list($source, $offset) = CodeHelper::offsetFromCode($source);
        $reflector = $this->createReflector($source);
        $offset = $reflector->reflectOffset(SourceCode::fromString($source), Offset::fromInt($offset));
        $assertion($offset->frame());
    }

    public function provideBuildForNode()
    {
        return [
            'Exceptions' => [
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
                    $this->assertEquals(Type::fromString('Exception'), $exception->symbolInformation()->type());
                }
            ],
            'Respects closure scope' => [
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
                    $this->assertCount(1, $frame->locals()->byName('$bar'));
                    $this->assertCount(0, $frame->locals()->byName('$foo'));
                }
            ],
            'Injects closure parameters' => [
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
                    $this->assertEquals(Type::fromString('Foobar'), $variable->symbolInformation()->type());
                }
            ],
            'Injects imported closure parent scope variables' => [
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
                    $this->assertEquals('string', (string) $zed->symbolInformation()->type());
                    $this->assertEquals(Symbol::VARIABLE, $zed->symbolInformation()->symbol()->symbolType());
                }
            ],
            'Injects variables with @var (non-standard)' => [
                <<<'EOT'
<?php
/** @var $zed string */
$zed;
<>
EOT
                ,
                function (Frame $frame) {
                    $this->assertCount(1, $frame->locals()->byName('$zed'));
                    $this->assertEquals('string', (string) $frame->locals()->byName('$zed')->last()->symbolInformation()->type());
                }
            ],
            'Injects variables with @var (standard)' => [
                <<<'EOT'
<?php
/** @var string $zed */
$zed;
<>
EOT
                ,
                function (Frame $frame) {
                    $this->assertCount(1, $frame->locals()->byName('$zed'));
                    $this->assertEquals('string', (string) $frame->locals()->byName('$zed')->last()->symbolInformation()->type());
                }
            ],
            'Handles array assignments' => [
                <<<'EOT'
<?php
$foo = [ 'foo' => 'bar' ];
$bar = $foo['foo'];
<>
EOT
                ,
                function (Frame $frame) {
                    $this->assertCount(2, $frame->locals());
                    $this->assertEquals('array', (string) $frame->locals()->first()->symbolInformation()->type());
                    $this->assertEquals(['foo' => 'bar'], $frame->locals()->first()->symbolInformation()->value());
                    $this->assertEquals('string', (string) $frame->locals()->last()->symbolInformation()->type());
                    $this->assertEquals('bar', (string) $frame->locals()->last()->symbolInformation()->value());
                }
            ]
        ];
    }
}

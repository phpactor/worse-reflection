<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Reflection\Inference;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Inference\Frame;

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
            'It returns type for parent' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar extends Barfoo
{
    public function hello()
    {
    }
}

class Barfoo {}
EOT
            , [ 'Foobar\Barfoo\Foobar', 'hello' ], function (Frame $frame) {
                $this->assertCount(1, $frame->locals()->byName('parent'));
                $this->assertEquals(
                    Type::fromString('Foobar\Barfoo\Barfoo'),
                    $frame->locals()->byName('parent')->first()->value()->type()
                );
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
                $vars = $frame->locals()->byName('$this');
                $this->assertCount(1, $vars);
                $value = $vars->first()->value();
                $this->assertEquals('Foobar', (string) $value->type());
            }],
            'It returns type for self' => [
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
                $vars = $frame->locals()->byName('self');
                $this->assertCount(1, $vars);
                $value = $vars->first()->value();
                $this->assertEquals('Foobar', (string) $value->type());
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
                $value = $vars->first()->value();
                $this->assertEquals('string', (string) $value->type());
                $this->assertEquals('foobar', (string) $value->value());
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
                $vars = $frame->locals()->byName('$foobar');
                $this->assertCount(1, $vars);
                $value = $vars->first()->value();
                $this->assertEquals('Foobar', (string) $value->type());
            }],
        ];
    }
}

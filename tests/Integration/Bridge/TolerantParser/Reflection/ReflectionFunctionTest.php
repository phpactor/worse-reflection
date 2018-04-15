<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;
use Closure;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Type;

class ReflectionFunctionTest extends IntegrationTestCase
{
    const TEST_FUNCTION_NAME = 'hello';

    /**
     * @dataProvider provideReflectsFunction
     */
    public function testReflects(string $source, Closure $assertion)
    {
        $functions = $this->createReflector($source)->reflectFunctionsIn($source);
        $assertion($functions->get(self::TEST_FUNCTION_NAME));
    }

    public function provideReflectsFunction()
    {
        yield 'single function with no params' => [
            <<<'EOT'
<?php
function hello()
{
}
EOT
            , function (ReflectionFunction $function) {
                $this->assertEquals(self::TEST_FUNCTION_NAME, $function->name());
                $this->assertEquals(Position::fromStartAndEnd(6, 26), $function->position());
            }
        ];

        yield 'function\'s frame' => [
            <<<'EOT'
<?php
function hello()
{
    $hello = 'hello';
}
EOT
            , function (ReflectionFunction $function) {
                $this->assertCount(1, $function->frame()->locals());
            }
        ];

        yield 'the docblock' => [
            <<<'EOT'
<?php
/** Hello */
function hello()
{
    $hello = 'hello';
}
EOT
            , function (ReflectionFunction $function) {
                $this->assertEquals('/** Hello */', trim($function->docblock()->raw()));
            }
        ];

        yield 'the declared scalar type' => [
            <<<'EOT'
<?php
function hello(): string {}
EOT
            , function (ReflectionFunction $function) {
                $this->assertEquals('string', $function->type()->short());
            }
        ];

        yield 'the declared class type' => [
            <<<'EOT'
<?php
use Foobar\Barfoo;
function hello(): Barfoo {}
EOT
            , function (ReflectionFunction $function) {
                $this->assertEquals('Foobar\Barfoo', $function->type()->className()->full());
            }
        ];

        yield 'unknown if nothing declared as type' => [
            <<<'EOT'
<?php
function hello() {}
EOT
            , function (ReflectionFunction $function) {
                $this->assertEquals(Type::unknown(), $function->type());
            }
        ];

        yield 'type from docblock' => [
            <<<'EOT'
<?php
/**
 * @return string
 */
function hello() {}
EOT
            , function (ReflectionFunction $function) {
                $this->assertEquals(Type::string(), $function->inferredTypes()->best());
            }
        ];
    }
}

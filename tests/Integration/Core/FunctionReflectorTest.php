<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use Closure;
use PHPStan\Broker\FunctionNotFoundException;
use Phpactor\WorseReflection\Core\Exception\FunctionNotFound;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\WorseReflection\Core\Exception\UnexpectedReflectionType;
use Phpactor\TestUtils\ExtractOffset;

class FunctionReflectorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectFunction
     */
    public function testReflectFunction(string $source, string $name, Closure $assertion)
    {
        $reflection = $this->createReflector($source)->reflectFunction($name);
        $assertion($reflection);
    }

    public function provideReflectFunction()
    {
        yield 'reflect function' => [
            '<?php function hello() {}',
            'hello',
            function (ReflectionFunction $function) {
                $this->assertEquals('hello', $function->name());
            }
        ];
    }

    public function testThrowsExceptionIfFunctionNotFound()
    {
        $this->expectException(FunctionNotFound::class);
        $this->createReflector('<?php ')->reflectFunction('hallo');
    }
}

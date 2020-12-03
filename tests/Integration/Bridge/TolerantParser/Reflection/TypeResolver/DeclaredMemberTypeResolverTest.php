<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class DeclaredMemberTypeResolverTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideResolveTypes
     */
    public function testResolveTypes(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString($class));
        $assertion($class->properties()->get('p'));
    }

    public function provideResolveTypes(): \Generator
    {
        yield 'union type' => [
            '<?php class C { private int|string $p; }',
                'C',
                function (ReflectionProperty $property) {
                    $this->assertEquals(Types::fromTypes([
                        Type::int(),
                        Type::string(),
                    ]), $property->inferredTypes());
                },
        ];

        yield 'union type with FQN' => [
            '<?php class C { private int|Foobar|Baz $p; }',
                'C',
                function (ReflectionProperty $property) {
                    $this->assertEquals(Types::fromTypes([
                        Type::int(),
                        Type::class('Foobar'),
                        Type::class('Baz'),
                    ]), $property->inferredTypes());
                },
        ];
    }
}

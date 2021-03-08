<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\WorseReflection\Bridge\Phpactor\DocBlockParserTypeResolver;
use Phpactor\WorseReflection\Bridge\Phpactor\DocBlockParserTypeResolverFactory;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeResolver;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeResolverFactory;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionType;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\GenericType;
use Phpactor\WorseReflection\Core\Type\IntegerType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\TemplatedType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class DocBlockParserTypeResolverTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideResolve
     */
    public function testResolve(string $docblock, ReflectionType $expected): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $class = $reflector->reflectClass('Bar\Foobar');

        $resolver = (new DocBlockParserTypeResolverFactory($reflector))->create($class, $docblock);

        self::assertEquals($expected, $resolver->resolveReturn());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideResolve(): Generator
    {
        yield [
            '/** @return string */',
            new StringType()
        ];

        yield [
            '/** @return int */',
            new IntegerType()
        ];

        yield [
            '/** @return float */',
            new FloatType()
        ];

        yield [
            '/** @return mixed */',
            new MixedType()
        ];

        yield [
            '/** @return array */',
            new ArrayType()
        ];

        yield [
            '/** @return array|string */',
            new UnionType([new ArrayType(), new StringType()])
        ];

        yield [
            '/** @return array<string> */',
            new ArrayType(new StringType())
        ];

        yield [
            '/** @return array<int, string> */',
            new ArrayType(new IntegerType(), new StringType())
        ];

        yield [
            '/** @template T @return T */',
            new TemplatedType('T')
        ];

        yield [
            '/** @template T of Foo @return T */',
            new TemplatedType('T', new ClassType(ClassName::fromString('Bar\Foo')))
        ];

        yield [
            '/** @return \IteratorAggregate<Foobar> */',
            new GenericType(new ClassType(
                ClassName::fromString('\IteratorAggregate')
            ), [new ClassType(
                ClassName::fromString('Bar\Foobar')
            )])
        ];
    }

    /**
     * @dataProvider provideResolveWithContext
     */
    public function testResolveWithContext(string $source, ReflectionType $expected): void
    {
        $reflector = $this->createReflector($source);
        $method = $reflector->reflectClass('Foobar')->methods()->get('foo');

        $resolver = (new DocBlockParserTypeResolverFactory($reflector))->create($method, $method->docblock()->raw());
        self::assertEquals($expected, $resolver->resolveReturn());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideResolveWithContext(): Generator
    {
        yield [
            '<?php /** @template T */class Foobar { /** @return T */public function foo() {} }',
            new TemplatedType('T')
        ];

        yield [
            <<<'EOT'
<?php

/**
 * @template X
 * @template Y
 */
class ParentClass
{
    /**
     * @return X
     */
    public function foo();
}

/** 
 * @extends ParentClass<int,string>
 */
class Foobar extends ParentClass
{
}
EOT,

            new TemplatedType('X')
        ];
    }
}

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
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class DocBlockParserTypeResolverTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideResolve
     */
    public function testResolve(string $docblock, ReflectionType $expected): void
    {
        $class = $this->createReflector('<?php namespace Bar; class Foobar{}')->reflectClass('Bar\Foobar');

        $resolver = (new DocBlockParserTypeResolverFactory())->create($class, $docblock);

        self::assertEquals($expected, $resolver->resolveReturn($docblock));
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
            '/** @return \IteratorAggregate<Foobar> */',
            new GenericType(new ClassType(
                ClassName::fromString('\IteratorAggregate')
            ), [new ClassType(
                ClassName::fromString('Bar\Foobar')
            )])
        ];
    }
}

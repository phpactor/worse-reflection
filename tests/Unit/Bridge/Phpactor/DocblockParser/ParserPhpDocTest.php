<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor\DocblockParser;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\PhpDoc;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\ParserPhpDocFactory;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\PhpDoc\DocBlockTypeResolver;
use Phpactor\WorseReflection\Core\PhpDoc\PhpDocFactory;
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

class ParserPhpDocTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideResolve
     */
    public function testResolve(string $docblock, ReflectionType $expected): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $scope = $reflector->reflectClass('Bar\Foobar')->scope();

        $docblock = (new ParserPhpDocFactory())->create($scope, $docblock);

        self::assertEquals($expected, $docblock->returnType());
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
            '/** @return T */',
            new ClassType(ClassName::fromString('Bar\T'))
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

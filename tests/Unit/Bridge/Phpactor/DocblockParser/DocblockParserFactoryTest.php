<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor\DocblockParser;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\ClassFileConverter\Domain\ClassName as PhpactorClassName;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\DocblockParser\Ast\Type\GenericNode;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\DocblockParserFactory;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\ParserPhpDocFactory;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\PhpDoc\DocBlockTypeResolver;
use Phpactor\WorseReflection\Core\PhpDoc\ExtendsTemplate;
use Phpactor\WorseReflection\Core\PhpDoc\PhpDoc as PhpactorPhpDoc;
use Phpactor\WorseReflection\Core\PhpDoc\PhpDocFactory;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\PhpDoc\Template;
use Phpactor\WorseReflection\Core\PhpDoc\Templates;
use Phpactor\WorseReflection\Core\Reflection\ReflectionType;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\TemplatedType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class DocblockParserFactoryTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideResolveType
     * @param Type|string $expected
     */
    public function testResolveType(string $docblock, $expected): void
    {
        $docblock = $this->parseDocblock($docblock);

        if (is_string($expected)) {
            self::assertEquals($expected, $docblock->returnTypes()->best()->__toString());
            return;
        }
        self::assertEquals($expected, $docblock->returnTypes()->best());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideResolveType(): Generator
    {
        yield [
            '/** @return string */',
            new StringType()
        ];

        yield [
            '/** @return int */',
            new IntType()
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
            new ArrayType(new MissingType())
        ];

        yield [
            '/** @return array|string */',
            new UnionType(new ArrayType(new MissingType()), new StringType())
        ];

        yield [
            '/** @return array<string> */',
            new ArrayType(new StringType())
        ];

        yield [
            '/** @return array<int, string> */',
            new ArrayType(new IntType(), new StringType())
        ];

        yield [
            '/** @return T */',
            'T',
        ];

        yield [
            '/** @return \IteratorAggregate<Foobar> */',
            'IteratorAggregate<Foobar>',
        ];
    }

    public function testMethods(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @method Barfoo foobar() */');
        $methods = $docblock->methods($reflector->reflectClass('Bar\Foobar'));

        self::assertEquals('foobar', $methods->first()->name());
        self::assertEquals('Barfoo', $methods->first()->type());
    }

    public function testMethodsWithParams(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @method Barfoo foobar(string $foobar, int $barfoo) */');
        $methods = $docblock->methods($reflector->reflectClass('Bar\Foobar'));

        self::assertEquals('foobar', $methods->first()->name());
        self::assertEquals('Barfoo', $methods->first()->type());
        self::assertEquals('foobar', $methods->first()->parameters()->first()->name());
        self::assertEquals('string', $methods->first()->parameters()->first()->type());
        self::assertEquals('barfoo', $methods->first()->parameters()->get('barfoo')->name());
        self::assertEquals('int', $methods->first()->parameters()->get('barfoo')->type());
    }

    public function testProperties(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @property Barfoo $foobar */');
        $methods = $docblock->properties($reflector->reflectClass('Bar\Foobar'));

        self::assertEquals('foobar', $methods->first()->name());
        self::assertEquals('Bar\Barfoo', $methods->first()->type()->__toString());
    }

    public function testVars(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @var Barfoo */');
        $vars = $docblock->vars();
        self::assertCount(1, $vars->types());
        self::assertEquals('Barfoo', $vars->types()->best());
    }

    public function testVarsWithName(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @var Barfoo $foo */');
        $vars = iterator_to_array($docblock->vars());
        self::assertCount(1, $vars);
        self::assertEquals('Barfoo', $vars[0]->types()->best());
        self::assertEquals('foo', $vars[0]->name());
    }

    public function testParameterTypes(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @param Barfoo $foobar */');
        $types = $docblock->parameterTypes('foobar');
        self::assertCount(1, $types);
    }

    public function testPropertyTypes(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @property Barfoo $foobar */');
        $types = $docblock->propertyTypes('foobar');
        self::assertCount(1, $types);
    }

    private function parseDocblock(string $docblock): DocBlock
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        return $this->parseDocblockWithReflector($reflector, $docblock);
    }

    private function parseDocblockWithReflector(Reflector $reflector, string $docblock): DocBlock
    {
        return (new DocblockParserFactory($reflector))->create($docblock);
    }

}

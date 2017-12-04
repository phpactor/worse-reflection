<?php

namespace Phpactor\WorseReflection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

class DocblockTest extends TestCase
{
    /**
     * @testdox It returns true for "none" if no docblock is present.
     */
    public function testNone()
    {
        $docblock = Docblock::fromString('');
        $this->assertFalse($docblock->isDefined());
    }

    /**
     * @dataProvider provideReturnTypes
     */
    public function testReturnTypes(string $docblock, array $expectedTypes)
    {
        $docblock = Docblock::fromString($docblock);
        $types = $docblock->returnTypes();
        $expectedTypes = array_map(function (string $type) {
            return Type::fromString($type);
        }, $expectedTypes);

        $this->assertEquals($expectedTypes, $types);
    }

    public function provideReturnTypes()
    {
        return [
            'None' => [
                '/**  */',
                [ ],
            ],
            'Single short' => [
                '/** @return Foobar */',
                [ 'Foobar' ],
            ],
            'Intersection' => [
                '/** @return Foobar&$this */',
                [ 'Foobar', '$this' ],
            ],
            'Multiple short' => [
                '/** @return Foobar|$this */',
                [ 'Foobar', '$this' ],
            ],
            'Multiple full' => [
                '/** @return \\Acme\\Bar\\Foobar|$this */',
                [ 'Acme\\Bar\\Foobar', '$this' ],
            ],
            'Multiple relative' => [
                '/** @return Bar\\Foobar|$this */',
                [ 'Bar\\Foobar', '$this' ],
            ],
        ];
    }

    /**
     * @dataProvider provideMethodTypes
     */
    public function testMethodTypes(string $docblock, array $expectedTypes)
    {
        $docblock = Docblock::fromString($docblock);
        $types = $docblock->methodTypes();
        $expectedTypes = array_map(function (string $type) {
            return Type::fromString($type);
        }, $expectedTypes);

        $this->assertEquals($expectedTypes, $types);
    }

    public function provideMethodTypes()
    {
        return [
            'None' => [
                '/**  */',
                [ ],
            ],
            'Single short' => [
                '/** @method Foobar foobar() */',
                [ 'foobar' => 'Foobar' ],
            ],
            'Single no parenthesis' => [
                '/** @method Foobar foobar */',
                [ 'foobar' => 'Foobar' ],
            ],
        ];
    }

    public function testPropertyTypes()
    {
        $docblock = '/** @var Foobar */';
        $docblock = Docblock::fromString($docblock);
        $types = $docblock->varTypes();

        $this->assertEquals([ Type::fromString('Foobar') ], $types);
    }
}

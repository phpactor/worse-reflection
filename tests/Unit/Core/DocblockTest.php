<?php

namespace Phpactor\WorseReflection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Type;

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
            'Itersection' => [
                '/** @return Foobar^$this */',
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
}

<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Namespace_;

class ClassNameTest extends \PHPUnit_Framework_TestCase
{
    public function testFromNamespaceAndShortName()
    {
        $this->assertEquals(
            'Foobar',
            ClassName::fromNamespaceAndShortName(
                Namespace_::fromString(''),
                'Foobar'
            )->getFqn()
        );
        $this->assertEquals(
            'Barfoo\\Foobar',
            ClassName::fromNamespaceAndShortName(
                Namespace_::fromString('Barfoo'),
                'Foobar'
            )->getFqn()
        );
        $this->assertEquals(
            'BarBar\\Barfoo\\Foobar',
            ClassName::fromNamespaceAndShortName(
                Namespace_::fromString('BarBar\\Barfoo'),
                'Foobar'
            )->getFqn()
        );
    }

    public function testGetShortName()
    {
        $this->assertEquals(
            'Barbar',
            ClassName::fromString('Barfoo\\Foobar\\Barbar')->getShortName()
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Class name must have at least one part
     */
    public function testExceptionOnEmpty()
    {
        ClassName::fromParts([]);
    }
}

<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\Name;
use DTL\WorseReflection\Namespace_;

class NameTest extends \PHPUnit_Framework_TestCase
{
    public function testFromNamespaceAndShortName()
    {
        $this->assertEquals(
            'Foobar',
            Name::fromNamespaceAndShortName(
                Namespace_::fromString(''),
                'Foobar'
            )->getFqn()
        );
        $this->assertEquals(
            'Barfoo\\Foobar',
            Name::fromNamespaceAndShortName(
                Namespace_::fromString('Barfoo'),
                'Foobar'
            )->getFqn()
        );
        $this->assertEquals(
            'BarBar\\Barfoo\\Foobar',
            Name::fromNamespaceAndShortName(
                Namespace_::fromString('BarBar\\Barfoo'),
                'Foobar'
            )->getFqn()
        );
    }

    public function testGetShortName()
    {
        $this->assertEquals(
            'Barbar',
            Name::fromString('Barfoo\\Foobar\\Barbar')->getShortName()
        );
    }
}

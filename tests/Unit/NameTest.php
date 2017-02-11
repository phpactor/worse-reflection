<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\Name;
use DTL\WorseReflection\NamespaceName;

class NameTest extends \PHPUnit_Framework_TestCase
{
    public function testFromNamespaceAndShortName()
    {
        $this->assertEquals(
            'Foobar',
            Name::fromNamespaceAndShortName(
                NamespaceName::fromString(''),
                'Foobar'
            )->getFqn()
        );
        $this->assertEquals(
            'Barfoo\\Foobar',
            Name::fromNamespaceAndShortName(
                NamespaceName::fromString('Barfoo'),
                'Foobar'
            )->getFqn()
        );
        $this->assertEquals(
            'BarBar\\Barfoo\\Foobar',
            Name::fromNamespaceAndShortName(
                NamespaceName::fromString('BarBar\\Barfoo'),
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

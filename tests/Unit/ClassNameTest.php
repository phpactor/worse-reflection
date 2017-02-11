<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\Name;
use DTL\WorseReflection\ClassName;

class ClassNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Class name must have at least one part
     */
    public function testExceptionOnEmpty()
    {
        ClassName::fromParts([]);
    }
}

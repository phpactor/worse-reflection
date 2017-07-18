<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Formatted;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Docblock;

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
}

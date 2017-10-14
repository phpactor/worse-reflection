<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Offset;

class OffsetTest extends TestCase
{
    const OFFSET = 123;

    public function testFromUnknownReturnsOffsetIfGivenOffset()
    {
        $givenOffset = Offset::fromInt(self::OFFSET);
        $offset = Offset::fromUnknown($givenOffset);

        $this->assertSame($givenOffset, $offset);
    }

    public function testFromUnknownString()
    {
        $offset = Offset::fromUnknown(self::OFFSET);

        $this->assertEquals(Offset::fromInt(self::OFFSET), $offset);
    }

    public function testFromUnknownInvalid()
    {
        $this->expectExceptionMessage('Do not know how to create offset');
        Offset::fromUnknown(new \stdClass);
    }
}

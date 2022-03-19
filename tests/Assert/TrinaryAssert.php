<?php

namespace Phpactor\WorseReflection\Tests\Assert;

use Phpactor\WorseReflection\Core\Trinary;

trait TrinaryAssert
{
    public static function assertTrinaryTrue(Trinary $trinary): void
    {
        self::assertEquals(Trinary::true(), $trinary);
    }
}

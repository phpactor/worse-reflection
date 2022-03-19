<?php

namespace Phpactor\WorseReflection\Tests\Trait;

use Phpactor\WorseReflection\Core\Trinary;

trait TrinaryTestTrait
{
    public static function assertTrinaryTrue(Trinary $trinary): void
    {
        self::assertEquals(Trinary::true(), $trinary);
    }
}

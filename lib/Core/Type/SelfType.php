<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class SelfType implements Type
{
    public function __toString(): string
    {
        return 'self';
    }
}

<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class ObjectType implements Type
{
    public function __toString(): string
    {
        return 'object';
    }

    public function toPhpString(): string
    {
        return $this->__toString();
    }
}

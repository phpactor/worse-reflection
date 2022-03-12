<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class NullableType implements Type
{
    public Type $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function __toString(): string
    {
        return '?' . $this->type->__toString();
    }
}

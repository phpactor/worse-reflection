<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class IterableType implements Type
{
    public Type $keyType;
    public Type $valueType;

    public function __construct(Type $keyType, Type $valueType)
    {
        $this->keyType = $keyType;
        $this->valueType = $valueType;
    }

    public function __toString(): string
    {
        return 'iterable';
    }
}

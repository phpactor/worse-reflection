<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class IterableType implements Type
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
        if ((!$this->valueType instanceof MissingType) && $this->keyType instanceof MissingType) {
            return sprintf('%s[]', $this->valueType->__toString());
        }
        return 'iterable';
    }
}

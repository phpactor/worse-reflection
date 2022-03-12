<?php

namespace Phpactor\WorseReflection\Core\Type;

class ArrayType extends IterableType
{
    public function __toString(): string
    {
        if ((!$this->valueType instanceof MissingType) && $this->keyType instanceof MissingType) {
            return sprintf('%s[]', $this->valueType->__toString());
        }
        return 'array';
    }
}

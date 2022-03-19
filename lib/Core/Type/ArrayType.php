<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class ArrayType implements Type, IterableType
{
    public Type $valueType;

    public function __construct(Type $valueType)
    {
        $this->valueType = $valueType;
    }

    public function __toString(): string
    {
        if ($this->valueType instanceof MissingType) {
            return $this->toPhpString();
        }
        return sprintf('%s[]', $this->valueType->__toString());
    }

    public function toPhpString(): string
    {
        return 'array';
    }

    public function iterableValueType(): Type
    {
        return $this->valueType;
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::fromBoolean($type instanceof ArrayType);
    }
}

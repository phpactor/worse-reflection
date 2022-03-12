<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

/**
 * NOTE: This class will be replaced by generics
 */
class CollectionType extends IterableType
{
    public Type $classType;

    public Type $valueType;

    public function __construct(Type $classType, Type $valueType)
    {
        $this->classType = $classType;
        $this->valueType = $valueType;
    }

    public function __toString(): string
    {
        return sprintf('%s<%s>', $this->classType, $this->valueType);
    }

    public function toPhpString(): string
    {
        return $this->classType->__toString();
    }
}

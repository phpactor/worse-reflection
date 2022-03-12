<?php

namespace Phpactor\WorseReflection\Core\Type;

abstract class ScalarType extends PrimitiveType
{
    public function toPhpString(): string
    {
        return $this->__toString();
    }
}

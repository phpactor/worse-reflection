<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class VoidType extends PrimitiveType
{
    public function __toString(): string
    {
        return 'void';
    }

    public function toPhpString(): string
    {
        return $this->__toString();
    }
}

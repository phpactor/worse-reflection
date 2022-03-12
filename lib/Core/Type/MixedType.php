<?php

namespace Phpactor\WorseReflection\Core\Type;

final class MixedType extends PrimitiveType
{
    public function __toString(): string
    {
        return 'mixed';
    }
}

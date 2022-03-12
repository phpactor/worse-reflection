<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

final class BooleanType extends ScalarType
{
    public Trinary $value;

    public function __construct(?Trinary $value = null)
    {
        $this->value = $value ?? Trinary::maybe();
    }

    public function __toString(): string
    {
        return 'bool';
    }
}

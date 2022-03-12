<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class FloatType implements Type
{
    public ?float $value;

    public function __construct(?float $value)
    {
        $this->value = $value;
    }
}

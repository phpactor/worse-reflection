<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class FloatType implements Type
{
    public ?int $value;

    public function __construct(?int $value)
    {
        $this->value = $value;
    }
}

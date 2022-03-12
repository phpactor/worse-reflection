<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

final class BooleanType implements Type
{
    public Trinary $value;

    public function __construct(Trinary $value)
    {
        $this->value = $value;
    }
}

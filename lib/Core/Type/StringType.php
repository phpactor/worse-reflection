<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class StringType implements Type
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}

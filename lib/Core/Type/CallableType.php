<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class CallableType implements Type
{
    public array $args;
    public Type $returnType;

    public function __construct(array $args, Type $returnType)
    {
        $this->args = $args;
        $this->returnType = $returnType;
    }
}

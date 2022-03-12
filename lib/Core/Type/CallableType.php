<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class CallableType extends PrimitiveType
{
    public array $args;
    public Type $returnType;

    public function __construct(array $args, Type $returnType)
    {
        $this->args = $args;
        $this->returnType = $returnType;
    }

    public function __toString(): string
    {
        return sprintf('(...): %s', $this->returnType->__toString());
    }

    public function toPhpString(): string
    {
        return 'callable';
    }
}

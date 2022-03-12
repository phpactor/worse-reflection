<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class UnionType implements Type
{
    public array $types;

    public function __construct(Type ...$types)
    {
        $this->types = $types;
    }

    public function __toString(): string
    {
        return sprintf('Union<%s>', array_map(fn (Type $type) => $type->__toString(), $this->types));
    }
}

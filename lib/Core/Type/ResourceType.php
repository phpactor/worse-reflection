<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class ResourceType extends PrimitiveType
{
    public function __toString(): string
    {
        return 'resource';
    }
}

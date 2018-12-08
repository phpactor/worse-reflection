<?php

namespace Phpactor\WorseReflection\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;

class VirtualReflectionMethodCollection extends VirtualReflectionMemberCollection implements ReflectionMethodCollection
{
    protected function collectionType(): string
    {
        return ReflectionMethodCollection::class;
    }
}

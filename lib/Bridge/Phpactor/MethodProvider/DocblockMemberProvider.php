<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\MethodProvider;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class DocblockMemberProvider implements ReflectionMemberProvider
{
    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        return VirtualReflectionMemberCollection::fromMembers(iterator_to_array($class->docblock()->methods($class)));
    }
}

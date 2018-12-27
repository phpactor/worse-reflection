<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\MethodProvider;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMethodProvider;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class DocblockMethodProvider implements ReflectionMethodProvider
{
    public function provideMethods(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMethodCollection
    {
        return $class->docblock()->methods($class);
    }
}

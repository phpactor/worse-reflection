<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;

interface ReflectionMethodProvider
{
    public function provideMethods(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMethodCollection;
}

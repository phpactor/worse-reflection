<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

interface ReflectionMethodProvider
{
    public function provideMethods(ReflectionClassLike $class): ReflectionMethodCollection;
}

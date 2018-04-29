<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;

interface FunctionReflector
{
    public function reflectFunction($name): ReflectionFunction;
}

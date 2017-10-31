<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionNamespaceCollection;

interface ReflectionSourceCode
{
    public function position(): Position;

    public function namespaces(): ReflectionNamespaceCollection;
}

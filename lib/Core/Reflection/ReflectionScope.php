<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

interface ReflectionScope
{
    public function nameImports(): NameImports;

    public function resolveFullyQualifiedName(string $type, ReflectionClassLike $classLike);
}

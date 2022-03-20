<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionPropertyCollection;

class EmptyDocblock implements DocBlock
{
    public function methodTypes(string $methodName): Types
    {
        return Types::empty();
    }

    public function inherits(): bool
    {
        return false;
    }

    public function vars(): DocBlockVars
    {
        return new DocBlockVars([]);
    }

    public function parameterTypes(string $paramName): Types
    {
        return Types::empty();
    }

    public function propertyTypes(string $methodName): Types
    {
        return Types::empty();
    }

    public function formatted(): string
    {
        return '';
    }

    public function returnTypes(): Types
    {
        return Types::empty();
    }


    public function raw(): string
    {
        return '';
    }

    public function isDefined(): bool
    {
        return false;
    }

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection
    {
        return VirtualReflectionPropertyCollection::fromReflectionProperties([]);
    }

    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection
    {
        return VirtualReflectionMethodCollection::fromReflectionMethods([]);
    }

    public function deprecation(): Deprecation
    {
        return new Deprecation(false);
    }
}

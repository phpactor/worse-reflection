<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Node;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;

class ParsedDocblock implements DocBlock
{
    private Node $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function methodTypes(string $methodName): Types
    {
    }

    public function inherits(): bool
    {
    }

    public function vars(): DocBlockVars
    {
    }

    public function parameterTypes(string $paramName): Types
    {
    }

    public function propertyTypes(string $methodName): Types
    {
    }

    public function formatted(): string
    {
    }

    public function returnTypes(): Types
    {
    }


    public function raw(): string
    {
    }

    public function isDefined(): bool
    {
    }

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection
    {
    }

    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection
    {
    }
    public function deprecation(): Deprecation
    {
    }
}

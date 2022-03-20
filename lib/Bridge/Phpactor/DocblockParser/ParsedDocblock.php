<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Node;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
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

    private TypeConverter $typeConverter;

    public function __construct(Node $node, TypeConverter $typeConverter)
    {
        $this->node = $node;
        $this->typeConverter = $typeConverter;
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
        foreach ($this->node->descendantElements(ReturnTag::class) as $tag) {
            assert($tag instanceof ReturnTag);
            return Types::fromTypes([$this->typeConverter->convert($tag->type())]);
        }
        return Types::empty();
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

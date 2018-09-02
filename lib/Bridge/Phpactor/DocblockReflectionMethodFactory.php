<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\Docblock\DocblockType;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\Docblock\Tag\MethodTag;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Visibility;

class DocblockReflectionMethodFactory
{
    public function create(DocBlock $docblock, ReflectionClassLike $reflectionClass, MethodTag $methodTag)
    {
        $types = $this->typesFrom($reflectionClass, $methodTag->types());
        
        return new VirtualReflectionMethod(
            $reflectionClass->position(),
            $reflectionClass,
            $reflectionClass,
            $methodTag->methodName(),
            new Frame($methodTag->methodName()),
            $docblock,
            $reflectionClass->scope(),
            Visibility::public(),
            $types,
            Type::unknown(),
            VirtualReflectionParameterCollection::empty(),
            NodeText::fromString(''),
            false,
            false
        );
    }

    private function typesFrom(ReflectionClassLike $reflectionClass, DocblockTypes $docblockTypes)
    {
        $types = [];
        /** @var DocblockType $docblockType */
        foreach ($docblockTypes as $docblockType) {
            $types[] = Type::fromString($reflectionClass->scope()->resolveFullyQualifiedName($docblockType->__toString()));
        }

        return Types::fromTypes($types);
    }
}

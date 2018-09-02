<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\Docblock\DocblockType;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\Docblock\Method\Parameter;
use Phpactor\Docblock\Tag\MethodTag;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Visibility;

class DocblockReflectionMethodFactory
{
    public function create(DocBlock $docblock, ReflectionClassLike $reflectionClass, MethodTag $methodTag)
    {
        $types = $this->typesFrom($reflectionClass->scope(), $methodTag->types());
        $parameters = VirtualReflectionParameterCollection::empty();
        
        $reflectionMethod = new VirtualReflectionMethod(
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
            $parameters,
            NodeText::fromString(''),
            false,
            false
        );

        $this->parametersFrom($parameters, $reflectionMethod, $methodTag);

        return $reflectionMethod;
    }

    private function typesFrom(ReflectionScope $scope, DocblockTypes $docblockTypes)
    {
        $types = [];
        /** @var DocblockType $docblockType */
        foreach ($docblockTypes as $docblockType) {
            $types[] = Type::fromString($scope->resolveFullyQualifiedName($docblockType->__toString()));
        }

        return Types::fromTypes($types);
    }

    private function parametersFrom(VirtualReflectionParameterCollection $parameterCollection, VirtualReflectionMethod $reflectionMethod, MethodTag $methodTag)
    {
        /** @var Parameter $parameter */
        foreach ($methodTag->parameters() as $parameter) {
            $parameterCollection->add(new VirtualReflectionParameter(
                $parameter->name(),
                $reflectionMethod,
                $this->typesFrom($reflectionMethod->scope(), $parameter->types()),
                Type::unknown(),
                DefaultValue::fromValue($parameter->defaultValue()->value()),
                false,
                $reflectionMethod->scope(),
                $reflectionMethod->position()
            ));
        }
    }
}

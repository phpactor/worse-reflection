<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\OldDocblock;

use Phpactor\Docblock\DocblockType;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\Docblock\Method\Parameter;
use Phpactor\Docblock\Tag\MethodTag;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\TypeFactory;
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

        $originalMethod = $reflectionClass->methods()->has($methodTag->methodName()) ?
            $reflectionClass->methods()->get($methodTag->methodName()) : null;
        
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
            $originalMethod ? $originalMethod->type() : TypeFactory::unknown(),
            $parameters,
            NodeText::fromString(''),
            false,
            $methodTag->isStatic(),
            new Deprecation(false)
        );

        $this->parametersFrom($parameters, $reflectionMethod, $methodTag);

        return $reflectionMethod;
    }

    private function typesFrom(ReflectionScope $scope, DocblockTypes $docblockTypes)
    {
        $types = [];
        /** @var DocblockType $docblockType */
        foreach ($docblockTypes as $docblockType) {
            $types[] = $scope->resolveFullyQualifiedName($docblockType->__toString());
        }

        return Types::fromTypes($types);
    }

    private function parametersFrom(VirtualReflectionParameterCollection $parameterCollection, VirtualReflectionMethod $reflectionMethod, MethodTag $methodTag): void
    {
        /** @var Parameter $parameter */
        foreach ($methodTag->parameters() as $parameter) {
            $parameterCollection->add(new VirtualReflectionParameter(
                $parameter->name(),
                $reflectionMethod,
                $this->typesFrom($reflectionMethod->scope(), $parameter->types()),
                TypeFactory::unknown(),
                $parameter->defaultValue()->isDefined() ? DefaultValue::fromValue($parameter->defaultValue()->value()) : DefaultValue::undefined(),
                false,
                $reflectionMethod->scope(),
                $reflectionMethod->position()
            ));
        }
    }
}

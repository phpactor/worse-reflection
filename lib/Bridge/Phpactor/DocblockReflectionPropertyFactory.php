<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\Docblock\DocblockType;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\Docblock\Tag\PropertyTag;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;
use RuntimeException;

class DocblockReflectionPropertyFactory
{
    public function create(DocBlock $docblock, ReflectionClassLike $reflectionClass, PropertyTag $propertyTag): VirtualReflectionProperty
    {
        $types = $this->typesFrom($reflectionClass->scope(), $propertyTag->types());
        $parameters = VirtualReflectionParameterCollection::empty();
        ;

        if (
            !$reflectionClass instanceof ReflectionClass &&
            !$reflectionClass instanceof ReflectionTrait
        ) {
            throw new RuntimeException(sprintf(
                'Docblock properties can only be available on classes and traits, got "%s"',
                get_class($reflectionClass)
            ));
        }

        $originalProperty = $reflectionClass->properties()->has($propertyTag->propertyName()) ?
            $reflectionClass->properties()->get($propertyTag->propertyName()) : null;
        
        $reflectionProperty = new VirtualReflectionProperty(
            $reflectionClass->position(),
            $reflectionClass,
            $reflectionClass,
            $propertyTag->propertyName(),
            new Frame($propertyTag->propertyName()),
            $docblock,
            $reflectionClass->scope(),
            Visibility::public(),
            $types,
            $originalProperty ? $originalProperty->type() : TypeFactory::unknown(),
            new Deprecation(false)
        );

        return $reflectionProperty;
    }

    private function typesFrom(ReflectionScope $scope, DocblockTypes $docblockTypes)
    {
        $types = [];
        /** @var DocblockType $docblockType */
        foreach ($docblockTypes as $docblockType) {
            $types[] = TypeFactory::fromString($scope->resolveFullyQualifiedName($docblockType->__toString()));
        }

        return Types::fromTypes($types);
    }
}

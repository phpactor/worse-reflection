<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

class MemberTypeResolver
{
    const TYPE_METHODS = 'methods';
    const TYPE_CONSTANTS = 'constants';
    const TYPE_PROPERTIES = 'properties';

    
    private ClassReflector $reflector;

    public function __construct(ClassReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function methodType(Type $containerType, SymbolContext $info, string $name): SymbolContext
    {
        return $this->memberType(self::TYPE_METHODS, $containerType, $info, $name);
    }

    public function constantType(Type $containerType, SymbolContext $info, string $name): SymbolContext
    {
        return $this->memberType(self::TYPE_CONSTANTS, $containerType, $info, $name);
    }

    public function propertyType(Type $containerType, SymbolContext $info, string $name): SymbolContext
    {
        if (mb_substr($name, 0, 1) == '$') {
            $name = mb_substr($name, 1);
        }
        return $this->memberType(self::TYPE_PROPERTIES, $containerType, $info, $name);
    }

    /**
     * @return ReflectionClassLike
     */
    private function reflectClassOrNull(Type $containerType, string $name)
    {
        return $this->reflector->reflectClassLike($containerType->className());
    }

    private function memberType(string $memberType, Type $containerType, SymbolContext $info, string $name)
    {
        if (false === $containerType->isDefined()) {
            return $info->withIssue(sprintf(
                'No type available for containing class "%s" for method "%s"',
                (string) $containerType,
                $name
            ));
        }

        if (false === $containerType->isClass()) {
            return $info->withIssue(sprintf(
                'Containing type is not a class, got "%s"',
                (string) $containerType
            ));
        }

        try {
            $class = $this->reflectClassOrNull($containerType, $name);
        } catch (NotFound $e) {
            $info = $info->withIssue(sprintf(
                'Could not find container class "%s" for "%s"',
                (string) $containerType,
                $name
            ));

            return $info;
        }

        $info = $info->withContainerType(Type::class($class->name()));

        if (!method_exists($class, $memberType)) {
            $info = $info->withIssue(sprintf(
                'Container class "%s" has no method "%s"',
                (string) $containerType,
                $memberType
            ));

            return $info;
        }

        try {
            if (false === $class->$memberType()->has($name)) {
                $info = $info->withIssue(sprintf(
                    'Class "%s" has no %s named "%s"',
                    (string) $containerType,
                    $memberType,
                    $name
                ));

                return $info;
            }
        } catch (NotFound $e) {
            $info = $info->withIssue($e->getMessage());
            return $info;
        }

        $member = $class->$memberType()->get($name);
        assert($member instanceof ReflectionMember);
        $declaringClass = $member->declaringClass();

        $info = $info->withContainerType(Type::class($declaringClass->name()));

        return $info->withTypes($member->inferredTypes());
    }
}

<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use RuntimeException;

class MemberTypeResolver
{
    const TYPE_METHODS = 'methods';
    const TYPE_CONSTANTS = 'constants';
    const TYPE_PROPERTIES = 'properties';

    /**
     * @var ClassReflector
     */
    private $reflector;

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
            return $info;
        }

        if (false === $containerType->isClass()) {
            return $info->withProblem(new Problem(
                Problem::UNDEFINED,
                sprintf(
                    'Containing type is not a class, got "%s"',
                    (string) $containerType
                ),
                $info->symbol()->position()->start(),
                $info->symbol()->position()->end(),
            ));
        }

        try {
            $class = $this->reflectClassOrNull($containerType, $name);
        } catch (NotFound $e) {
            return $info->withProblem(new Problem(
                Problem::CLASS_NOT_FOUND,
                sprintf(
                    'Class "%s" not found for member "%s"',
                    (string) $containerType,
                    $name
                ),
                $info->symbol()->position()->start(),
                $info->symbol()->position()->end(),
            ));
        }

        $info = $info->withContainerType(Type::class($class->name()));

        if (!method_exists($class, $memberType)) {
            throw new RuntimeException(sprintf(
                'Container class "%s" has no method "%s"',
                (string) $containerType,
                $memberType
            ));

            return $info;
        }

        if (false === $class->$memberType()->has($name)) {
            $info = $info->withProblem(new Problem(
                Problem::UNDEFINED,
                sprintf(
                    'Class "%s" has no %s named "%s"',
                    (string) $containerType,
                    $memberType,
                    $name
                ),
                $info->symbol()->position()->start(),
                $info->symbol()->position()->end(),
            ));

            return $info;
        }

        $member = $class->$memberType()->get($name);
        assert($member instanceof ReflectionMember);
        $declaringClass = $member->declaringClass();

        $info = $info->withContainerType(Type::class($declaringClass->name()));

        return $info->withTypes($member->inferredTypes());
    }
}

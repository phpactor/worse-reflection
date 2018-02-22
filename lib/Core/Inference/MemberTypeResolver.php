<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;

class MemberTypeResolver
{
    const TYPE_METHODS = 'methods';
    const TYPE_CONSTANTS = 'constants';
    const TYPE_PROPERTIES = 'properties';

    /**
     * @var Reflector
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
        return $this->memberType(self::TYPE_PROPERTIES, $containerType, $info, $name);
    }

    /**
     * @return ReflectionClass
     */
    private function reflectClassOrNull(Type $containerType, string $name)
    {
        return $this->reflector->reflectClassLike(ClassName::fromString((string) $containerType));
    }

    private function memberType(string $type, Type $containerType, SymbolContext $info, string $name)
    {
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

        if (!method_exists($class, $type)) {
            $info = $info->withIssue(sprintf(
                'Container class "%s" has no method "%s"',
                (string) $containerType,
                $type
            ));

            return $info;
        }

        try {
            if (false === $class->$type()->has($name)) {
                $info = $info->withIssue(sprintf(
                    'Class "%s" has no %s named "%s"',
                    (string) $containerType,
                    $type,
                    $name
                ));

                return $info;
            }
        } catch (NotFound $e) {
            $info = $info->withIssue($e->getMessage());
            return $info;
        }

        /** @var $method ReflectionMethod */
        $method = $class->$type()->get($name);
        $declaringClass = $method->declaringClass();

        $info = $info->withContainerType(Type::class($declaringClass->name()));

        if ($type === self::TYPE_METHODS) {
            return $info->withTypes($method->inferredReturnTypes());
        }

        if ($type === self::TYPE_CONSTANTS) {
            return $info->withType($method->type());
        }

        if ($type === self::TYPE_PROPERTIES) {
            return $info->withTypes($method->inferredTypes());
        }
    }
}

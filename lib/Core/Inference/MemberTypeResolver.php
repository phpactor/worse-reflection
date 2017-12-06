<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod;

class MemberTypeResolver
{
    const TYPE_METHODS = 'methods';
    const TYPE_CONSTANTS = 'constants';
    const TYPE_PROPERTIES = 'properties';

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Reflector $reflector, Logger $logger)
    {
        $this->reflector = $reflector;
        $this->logger = $logger;
    }

    public function methodType(Type $containerType, SymbolInformation $info, string $name): SymbolInformation
    {
        return $this->memberType(self::TYPE_METHODS, $containerType, $info, $name);
    }

    public function constantType(Type $containerType, SymbolInformation $info, string $name): SymbolInformation
    {
        return $this->memberType(self::TYPE_CONSTANTS, $containerType, $info, $name);
    }

    public function propertyType(Type $containerType, SymbolInformation $info, string $name): SymbolInformation
    {
        return $this->memberType(self::TYPE_PROPERTIES, $containerType, $info, $name);
    }

    /**
     * @return ReflectionClass
     */
    private function reflectClassOrNull(Type $containerType, string $name)
    {
        try {
            return $this->reflector->reflectClassLike(ClassName::fromString((string) $containerType));
        } catch (NotFound $e) {
            $this->logger->warning(sprintf(
                'Unable to locate class "%s" for method "%s"',
                (string) $containerType,
                $name
            ));
        }
    }

    private function memberType(string $type, Type $containerType, SymbolInformation $info, string $name)
    {
        $class = $this->reflectClassOrNull($containerType, $name);

        if (null === $class) {
            return $info;
        }

        $info = $info->withContainerType(Type::class($class->name()));

        try {
            if (false === $class->$type()->has($name)) {
                $this->logger->warning(sprintf(
                    'Class "%s" has no method named "%s"',
                    (string) $containerType,
                    $name
                ));

                return $info;
            }
        } catch (NotFound $e) {
            $this->logger->warning($e->getMessage());
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

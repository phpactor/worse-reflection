<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

class MemberTypeResolver
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Reflector $reflector, Logger $logger, SymbolFactory $factory)
    {
        $this->reflector = $reflector;
        $this->logger = $logger;
    }

    public function methodType(Type $ownerType, SymbolInformation $info, string $name): SymbolInformation
    {
        $class = $this->reflectClassOrNull($ownerType, $name);

        if (null === $class) {
            return $info;
        }

        try {
            if (false === $class->methods()->has($name)) {
                $this->logger->warning(sprintf(
                    'Class "%s" has no method named "%s"',
                    (string) $ownerType,
                    $name
                ));
                return $info;
            }
        } catch (NotFound $e) {
            $this->logger->warning($e->getMessage());
            return $info;
        }

        /** @var $method ReflectionMethod */
        $method = $class->methods()->get($name);
        $declaringClass = $method->class();

        return $info
            ->withClassType(Type::class($declaringClass->name()))
            ->withType($method->inferredReturnType());
    }

    public function constantType(Type $ownerType, SymbolInformation $info, string $name): SymbolInformation
    {
        $class = $this->reflectClassOrNull($ownerType, $name);

        if (null === $class) {
            return $info;
        }

        try {
            if (false === $class->constants()->has($name)) {
                $this->logger->warning(sprintf(
                    'Class "%s" has no constant named "%s"',
                    (string) $ownerType,
                    $name
                ));
                return $info;
            }
        } catch (NotFound $e) {
            $this->logger->warning($e->getMessage());
            return $info;
        }

        $constant = $class->constants()->get($name);
        $declaringClass = $constant->class();

        return $info
            ->withClassType(Type::class($declaringClass->name()))
            ->withType($constant->type());
    }

    public function propertyType(Type $ownerType, SymbolInformation $info, string $name): SymbolInformation
    {
        $class = $this->reflectClassOrNull($ownerType, $name);

        if (null === $class) {
            return $info;
        }

        // interfaces do not have properties...
        if (false === $class instanceof ReflectionClass) {
            return $info;
        }

        try {
            if (false === $class->properties()->has($name)) {
                $this->logger->warning(sprintf(
                    'Class "%s" has no property named "%s"',
                    (string) $ownerType,
                    $name
                ));
                return $info;
            }
        } catch (NotFound $e) {
            $this->logger->warning($e->getMessage());
            return $info;
        }

        $property = $class->properties()->get($name);
        $declaringClass = $property->class();

        return $info
            ->withClassType(Type::class($declaringClass->name()))
            ->withType($property->type());
    }

    /**
     * @return ReflectionClass
     */
    private function reflectClassOrNull(Type $ownerType, string $name)
    {
        try {
            return $this->reflector->reflectClass(ClassName::fromString((string) $ownerType));
        } catch (NotFound $e) {
            $this->logger->warning(sprintf(
                'Unable to locate class "%s" for method "%s"',
                (string) $ownerType,
                $name
            ));
        }
    }
}

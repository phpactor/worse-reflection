<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;

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

    public function __construct(Reflector $reflector, Logger $logger)
    {
        $this->reflector = $reflector;
        $this->logger = $logger;
    }

    public function methodType(Type $ownerType, SymbolInformation $info, string $name): SymbolInformation
    {
        $info = $this->attachContainerClass($info, $ownerType, $name);

        if (null === $info->containerType()) {
            return $info;
        }

        try {
            if (false === $class->methods()->has($name)) {
                $message = sprintf(
                    'Class "%s" has no method named "%s"',
                    (string) $ownerType,
                    $name
                );
                $this->logger->warning($message);
                $info = $info->withContainerType(Type::class($class->name()));
                $info = $info->withError($message);

                return $info;
            }
        } catch (NotFound $e) {
            $this->logger->warning($e->getMessage());
            $info = $info->withError($message);
            return $info;
        }

        /** @var $method ReflectionMethod */
        $method = $class->methods()->get($name);
        $declaringClass = $method->declaringClass();

        return $info
            ->withContainerType(Type::class($declaringClass->name()))
            ->withTypes($method->inferredReturnTypes());
    }

    public function constantType(Type $ownerType, SymbolInformation $info, string $name): SymbolInformation
    {
        $info = $this->attachContainerClass($info, $ownerType, $name);

        if (null === $info->containerType()) {
            return $info;
        }

        try {
            if (false === $class->constants()->has($name)) {
                $this->logger->warning($message = sprintf(
                    'Class "%s" has no constant named "%s"',
                    (string) $ownerType,
                    $name
                ));
                $info = $info->withContainerType(Type::class($class->name()));
                $info = $info->withError($message);
                return $info;
            }
        } catch (NotFound $e) {
            $this->logger->warning($e->getMessage());
            $info = $info->withError($e->getMessage());
            return $info;
        }

        $constant = $class->constants()->get($name);
        $declaringClass = $constant->declaringClass();

        return $info
            ->withContainerType(Type::class($declaringClass->name()))
            ->withType($constant->type());
    }

    public function propertyType(Type $ownerType, SymbolInformation $info, string $name): SymbolInformation
    {
        $info = $this->attachContainerClass($info, $ownerType, $name);

        if (null === $info->containerType()) {
            return $info;
        }

        if ($class->isInterface()) {
            return $info;
        }

        try {
            if (false === $class->properties()->has($name)) {
                $this->logger->warning($message = sprintf(
                    'Class "%s" has no property named "%s"',
                    (string) $ownerType,
                    $name
                ));
                $info = $info->withContainerType(Type::class($class->name()));
                $info = $info->withError($message);
                return $info;
            }
        } catch (NotFound $e) {
            $this->logger->warning($e->getMessage());
            $info = $info->withError($e->getMessage());
            return $info;
        }

        $property = $class->properties()->get($name);
        $declaringClass = $property->declaringClass();

        return $info
            ->withContainerType(Type::class($declaringClass->name()))
            ->withTypes($property->inferredTypes());
    }

    /**
     * @return ReflectionClass
     */
    private function attachContainerClass(SymbolInformation $info, Type $ownerType, string $name): SymbolInformation
    {
        try {
            $class = $this->reflector->reflectClassLike(ClassName::fromString((string) $ownerType));
            $info = $info->withContainerType(Type::class($class->name()));
        } catch (NotFound $e) {
            $info = $info->withError(sprintf(
                'Could not find container class "%s" for member "%s"',
                (string) $ownerType, $name
            ));
        }

        return $info;
    }
}

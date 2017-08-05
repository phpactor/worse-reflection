<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Logger;
use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Exception\SourceNotFound;
use Phpactor\WorseReflection\Exception\ClassNotFound;
use Phpactor\WorseReflection\Reflection\ReflectionClass;

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

    public function methodType(Type $ownerType, string $name): Type
    {
        $class = null;
        try {
            $class = $this->reflector->reflectClass(ClassName::fromString((string) $ownerType));
        } catch (SourceNotFound $e) {
        } catch (ClassNotFound $e) {
        }

        if (null === $class) {
            $this->logger->warning(sprintf(
                'Unable to locate class "%s" for method "%s"', (string) $ownerType, $name
            ));
            return Type::unknown();
        }

        try {
            if (false === $class->methods()->has($name)) {
                $this->logger->warning(sprintf(
                    'Class "%s" has no method named "%s"',
                    (string) $ownerType, $name
                ));
                return Type::undefined();
            }
        } catch (SourceNotFound $e) {
            $this->logger->warning($e->getMessage());
            return Type::undefined();
        }

        return $class->methods()->get($name)->inferredReturnType();
    }

    public function constantType(Type $ownerType, string $name): Type
    {
        return Type::unknown();
    }

    public function propertyType(Type $ownerType, string $name): Type
    {
        $class = null;
        try {
            $class = $this->reflector->reflectClass(ClassName::fromString((string) $ownerType));
        } catch (SourceNotFound $e) {
        } catch (ClassNotFound $e) {
        }

        if (null === $class) {
            $this->logger->warning(sprintf(
                'Unable to locate class "%s" for property "%s"', (string) $ownerType, $name
            ));
            return Type::unknown();
        }

        // in the case that the class is an interface
        if (!$class instanceof ReflectionClass) {
            return Type::unknown();
        }

        try {
            if (false === $class->properties()->has($name)) {
                $this->logger->warning(sprintf(
                    'Class "%s" has no property named "%s"',
                    (string) $ownerType, $name
                ));
                return Type::undefined();
            }
        } catch (SourceNotFound $e) {
            $this->logger->warning($e->getMessage());
            return Type::undefined();
        }

        return $class->properties()->get($name)->type();
    }
}

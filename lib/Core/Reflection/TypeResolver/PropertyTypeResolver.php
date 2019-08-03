<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Generator;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;

class PropertyTypeResolver
{
    /**
     * @var ReflectionProperty
     */
    private $property;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(ReflectionProperty $property, Logger $logger)
    {
        $this->property = $property;
        $this->logger = $logger;
    }

    public function resolve(): Types
    {
        $docblockTypes = $this->getDocblockTypes();

        if (0 === $docblockTypes->count()) {
            $docblockTypes = $this->getDocblockTypesFromClass();
        }

        $resolvedTypes = array_map(function (Type $type) {
            return $this->property->scope()->resolveFullyQualifiedName($type, $this->property->class());
        }, iterator_to_array($docblockTypes));

        if (empty($resolvedTypes)) {
            foreach ($this->typeFromConstructor() as $type) {
                $resolvedTypes[] = $type;
            }
        }

        return Types::fromTypes($resolvedTypes);
    }

    private function getDocblockTypes(): Types
    {
        return $this->property->docblock()->vars()->types();
    }

    private function getDocblockTypesFromClass()
    {
        return $this->property->class()->docblock()->propertyTypes($this->property->name());
    }

    private function typeFromConstructor(): Generator
    {
        $declaringClass = $this->property->declaringClass();
        if (false === $declaringClass->methods()->has('__construct')) {
            return;
        }

        $constructor = $declaringClass->methods()->get('__construct');

        $parameters = $constructor->parameters();
        $frame = $constructor->frame();
        $propertyCandidates = $frame->properties()->byName($this->property->name());

        if (0 === $propertyCandidates->count()) {
            return;
        }

        foreach ($propertyCandidates->last()->symbolContext()->types() as $type) {
            yield $type;
        }
    }
}

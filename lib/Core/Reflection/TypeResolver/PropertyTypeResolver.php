<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Logger;

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

        $resolvedTypes = array_map(function (Type $type) {
            return $this->property->scope()->resolveFullyQualifiedName($type, $this->property->class());
        }, $docblockTypes);

        return Types::fromTypes($resolvedTypes);
    }

    private function getDocblockTypes()
    {
        return $this->property->docblock()->varTypes();
    }
}

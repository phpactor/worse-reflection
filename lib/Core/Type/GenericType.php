<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Reflection\ReflectionType;

class GenericType implements ReflectionType
{
    /**
     * @var ClassType
     */
    private $type;
    /**
     * @var ReflectionType[]
     */
    private $parameters;

    public function __construct(ClassType $type, array $parameters)
    {
        $this->type = $type;
        $this->parameters = $parameters;
    }
}

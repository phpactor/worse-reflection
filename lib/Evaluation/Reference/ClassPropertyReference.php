<?php

namespace DTL\WorseReflection\Evaluation\Reference;

class ClassPropertyReference extends ClassReference
{
    private $propertyName;

    public function __construct(string $className, string $propertyName)
    {
        parent::__construct($className);

        $this->propertyName = $propertyName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}


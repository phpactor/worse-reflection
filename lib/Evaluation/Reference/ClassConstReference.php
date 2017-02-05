<?php

namespace DTL\WorseReflection\Evaluation\Reference;

class ClassConstReference extends ClassReference
{
    private $constName;

    public function __construct(string $className, string $constName)
    {
        parent::__construct($className);

        $this->constName = $constName;
    }

    public function getConstantName(): string
    {
        return $this->constName;
    }
}

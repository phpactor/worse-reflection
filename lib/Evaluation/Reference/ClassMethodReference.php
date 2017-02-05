<?php

namespace DTL\WorseReflection\Evaluation\Reference;

class ClassMethodReference extends ClassReference
{
    private $methodName;

    public function __construct(string $className, string $methodName)
    {
        parent::__construct($className);

        $this->methodName = $methodName;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }
}


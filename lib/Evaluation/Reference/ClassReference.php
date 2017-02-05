<?php

namespace DTL\WorseReflection\Evaluation\Reference;

abstract class ClassReference
{
    private $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}

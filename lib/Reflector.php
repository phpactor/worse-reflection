<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\Reflection\ReflectionClass;
use DTL\WorseReflection\SourceContextFactory;
use PhpParser\Parser;

class Reflector
{
    private $classLocator;
    private $sourceContextFactory;

    public function __construct(ClassLocator $classLocator, SourceContextFactory $sourceContextFactory)
    {
        $this->classLocator = $classLocator;
        $this->sourceContextFactory = $sourceContextFactory;
    }


    public function reflectClass(ClassName $className): ReflectionClass
    {
        $source = $this->classLocator->locate($className);
        $sourceContext = $this->sourceContextFactory->createFor($source);

        if (false === $sourceContext->hasClass($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to locate class "%s" in file "%s"',
                $classFqn,
                $source->getLocation()
            ));
        }

        return new ReflectionClass($classFqn);
    }
}

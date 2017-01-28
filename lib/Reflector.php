<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\Reflection\ReflectionClass;
use DTL\WorseReflection\SourceContextFactory;
use PhpParser\Parser;

class Reflector
{
    private $sourceLocator;
    private $sourceContextFactory;

    public function __construct(SourceLocator $sourceLocator, SourceContextFactory $sourceContextFactory)
    {
        $this->sourceLocator = $sourceLocator;
        $this->sourceContextFactory = $sourceContextFactory;
    }


    public function reflectClass(ClassName $className): ReflectionClass
    {
        $source = $this->sourceLocator->locate($className);
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

    public function reflectString(string $string)
    {
        return $this->sourceContextFactory->createFor(Source::fromString($string));
    }
}

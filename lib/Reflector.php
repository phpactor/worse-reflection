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

        if (false === $sourceContext->hasClassNode($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to locate class "%s" in file "%s"',
                $className->getFqn(),
                $source->getLocation()
            ));
        }

        $classNode = $sourceContext->getClassNode($className);

        return new ReflectionClass($this, $sourceContext, $classNode);
    }

    public function reflectString(string $string)
    {
        return $this->sourceContextFactory->createFor(Source::fromString($string));
    }
}

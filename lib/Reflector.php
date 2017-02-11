<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\Reflection\ReflectionClass;
use DTL\WorseReflection\SourceContextFactory;
use PhpParser\Parser;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\Reflection\ReflectionFrame;
use DTL\WorseReflection\Frame\Visitor\FrameFinderVisitor;
use PhpParser\NodeTraverser;

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

        return $this->reflectClassFromSource($className, $source);
    }

    public function reflectFrame(Source $source, int $offset): ReflectionFrame
    {
        $sourceContext = $this->sourceContextFactory->createFor($source);
        $visitor = new FrameFinderVisitor($offset);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($sourceContext->getNodes());
        $frame = $visitor->getFrame();

        return new ReflectionFrame($this, $sourceContext, $frame);
    }

    public function reflectClassFromSource(ClassName $className, Source $source)
    {
        $sourceContext = $this->sourceContextFactory->createFor($source);

        if (false === $sourceContext->hasClass($className)) {
            throw new \RuntimeException(sprintf(
                'Unable to locate class "%s" in file "%s"',
                $className->getFqn(),
                (string) $source->getLocation()
            ));
        }

        $classNode = $sourceContext->getClassNode($className);

        return new ReflectionClass($this, $sourceContext, $classNode);
    }
}

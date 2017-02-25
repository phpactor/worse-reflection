<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\Reflection\ReflectionClass;
use DTL\WorseReflection\Reflection\ReflectionFrame;
use DTL\WorseReflection\Reflection\ReflectionOffset;
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
        return $this->getSourceContextForClass($className)->reflectClass($className);
    }

    public function reflectOffsetInSource(int $offset, Source $source): ReflectionOffset
    {
        $sourceContext = $this->sourceContextFactory->createForSource($this, $source);

        $visitor = new FrameFinderVisitor($offset);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($sourceContext->getNodes());
        $frame = $visitor->getFrame();
        $typeResolver = new TypeResolver($this, $sourceContext);

        return new ReflectionOffset(
            $typeResolver,
            $visitor->hasNodePathAtOffset() ? $visitor->getNodePathAtOffset(): null,
            new ReflectionFrame($this, $sourceContext, $frame)
        );
    }

    public function getSourceContextForClass(ClassName $className): SourceContext
    {
        $source = $this->sourceLocator->locate($className);

        return $this->getSourceContext($source);
    }

    public function getSourceContext(Source $source)
    {
        return $this->sourceContextFactory->createForSource($this, $source);
    }
}

<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\Reflection\ReflectionClass;
use DTL\WorseReflection\Reflection\ReflectionFrame;
use DTL\WorseReflection\Reflection\ReflectionOffset;
use DTL\WorseReflection\Frame\Visitor\FrameFinderVisitor;
use PhpParser\NodeTraverser;
use DTL\WorseReflection\SourceCode;
use DTL\WorseReflection\SourceCodeLocator;
use Microsoft\PhpParser\Parser;
use DTL\WorseReflection\Reflection\ReflectionSourceCode;
use DTL\WorseReflection\Reflection\AbstractReflectionClass;

class Reflector
{
    private $sourceLocator;
    private $parser;

    public function __construct(SourceCodeLocator $sourceLocator, Parser $parser = null)
    {
        $this->sourceLocator = $sourceLocator;
        $this->parser = $parser ?: new Parser();
    }

    public function reflectClass(ClassName $className): AbstractReflectionClass
    {
        $source = $this->sourceLocator->locate($className);
        $node = $this->parser->parseSourceFile((string) $source);
        $sourceCodeReflection = new ReflectionSourceCode($this, $node);

        if (null === $class = $sourceCodeReflection->findClass(ClassName::fromString($className))) {
            throw new \RuntimeException(sprintf(
                'Unable to locate class "%s"',
                $className->full()
            ));
        }

        return $class;
    }
}

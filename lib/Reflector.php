<?php

namespace DTL\WorseReflection;

use Microsoft\PhpParser\Parser;
use DTL\WorseReflection\Reflection\ReflectionSourceCode;
use DTL\WorseReflection\Reflection\AbstractReflectionClass;

class Reflector
{
    private $sourceLocator;
    private $parser;
    private $cache = [];

    public function __construct(SourceCodeLocator $sourceLocator, Parser $parser = null)
    {
        $this->sourceLocator = $sourceLocator;
        $this->parser = $parser ?: new Parser();
    }

    public function reflectClass(ClassName $className): AbstractReflectionClass
    {
        if (isset($this->cache[(string) $className])) {
            return $this->cache[(string) $className];
        }

        $source = $this->sourceLocator->locate($className);
        $node = $this->parser->parseSourceFile((string) $source);
        $sourceCodeReflection = new ReflectionSourceCode($this, $node);

        if (null === $class = $sourceCodeReflection->findClass(ClassName::fromString($className))) {
            throw new Exception\ClassNotFound(sprintf(
                'Unable to locate class "%s"',
                $className->full()
            ));
        }

        $this->cache[(string) $className] = $class;

        return $class;
    }
}

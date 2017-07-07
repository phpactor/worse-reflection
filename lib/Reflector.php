<?php

namespace Phpactor\WorseReflection;

use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Reflection\ReflectionSourceCode;
use Phpactor\WorseReflection\Reflection\AbstractReflectionClass;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionClassCollection;

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
        $classes = $this->reflectClassesIn($source);

        try {
            $class = $classes->get((string) $className);
        } catch (\InvalidArgumentException $e) {
            throw new Exception\ClassNotFound(sprintf(
                'Unable to locate class "%s"',
                $className->full()
            ), null, $e);
        }


        $this->cache[(string) $className] = $class;

        return $class;
    }

    public function reflectClassesIn(SourceCode $source): ReflectionClassCollection
    {
        $node = $this->parser->parseSourceFile((string) $source);

        return ReflectionClassCollection::fromSourceFileNode($this, $node);
    }
}

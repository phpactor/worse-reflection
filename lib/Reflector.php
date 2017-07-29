<?php

namespace Phpactor\WorseReflection;

use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Reflection\ReflectionSourceCode;
use Phpactor\WorseReflection\Reflection\AbstractReflectionClass;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Logger\ArrayLogger;

class Reflector
{
    private $sourceLocator;
    private $parser;
    private $cache = [];
    private $logger;

    public function __construct(SourceCodeLocator $sourceLocator, Parser $parser = null, Logger $logger = null)
    {
        $this->sourceLocator = $sourceLocator;
        $this->parser = $parser ?: new Parser();
        $this->logger = $logger ?: new ArrayLogger();
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

    public function logger(): Logger
    {
        return $this->logger;
    }
}

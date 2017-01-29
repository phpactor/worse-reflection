<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\Reflection\ReflectionMethod;
use PhpParser\Node;

abstract class AbstractReflectionCollection implements \IteratorAggregate
{
    private $reflector;
    private $sourceContext;
    private $indexedNodes = [];
    private $loadedReflections = [];

    public function __construct(
        string $context,
        string $nodeClass,
        Reflector $reflector,
        SourceContext $sourceContext,
        array $nodes
    )
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;

        foreach ($nodes as $node) {
            if (false === $node instanceof $nodeClass) {
                continue;
            }

            $this->indexedNodes[(string) $node->name] = $node;
        }
    }

    public function has($name): bool
    {
        return isset($this->indexedNodes[$name]);
    }

    public function get(string $name)
    {
        if (isset($this->loadedReflections[$name])) {
            return $this->loadedReflections[$name];
        }

        if (false === $this->has($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown %s "%s"', $this->context, $name
            ));
        }

        $this->loadedReflections[$name] = $this->createReflectionElement($this->reflector, $this->sourceContext, $this->indexedNodes[$name]);

        return $this->loadedReflections[$name];
    }

    public function all()
    {
        $methods = [];
        foreach (array_keys($this->indexedNodes) as $methodName) {
            $methods[] = $this->get($methodName);
        }

        return $methods;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    abstract protected function createReflectionElement(Reflector $reflector, SourceContext $sourceContext, Node $node);
}


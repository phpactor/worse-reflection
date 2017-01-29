<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\Reflection\ReflectionMethod;

class ReflectionMethodCollection implements \IteratorAggregate
{
    private $reflector;
    private $sourceContext;
    private $classNode;
    private $methodNodes = [];

    private $loadedMethods = [];

    public function __construct(Reflector $reflector, SourceContext $sourceContext, ClassLike $classNode)
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->classNode = $classNode;

        foreach ($this->classNode->stmts as $node) {
            if (false === $node instanceof ClassMethod) {
                continue;
            }

            $this->methodNodes[(string) $node->name] = $node;
        }
    }

    public function has($methodName)
    {
        return isset($this->methodNodes[$methodName]);
    }

    public function get(string $methodName): ReflectionMethod
    {
        if (isset($this->loadedMethods[$methodName])) {
            return $this->loadedMethods[$methodName];
        }

        if (false === $this->has($methodName)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown method "%s"', $methodName
            ));
        }

        $this->loadedMethods[$methodName] = new ReflectionMethod($this->reflector, $this->sourceContext, $this->methodNodes[$methodName]);

        return $this->loadedMethods[$methodName];
    }

    public function all()
    {
        $methods = [];
        foreach (array_keys($this->methodNodes) as $methodName) {
            $methods[] = $this->get($methodName);
        }

        return $methods;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }
}

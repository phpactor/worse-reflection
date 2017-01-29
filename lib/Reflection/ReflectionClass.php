<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\Class_;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\ClassName;

class ReflectionClass
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var SourceContext
     */
    private $sourceContext;

    /**
     * @var Class_
     */
    private $classNode;

    /*
     * @var ClassMethod[]
     */
    private $classMethodNodes;

    public function __construct(
        Reflector $reflector,
        SourceContext $sourceContext,
        Class_ $classNode
    )
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->classNode = $classNode;

        $this->classMethodNodes = array_filter($this->classNode->stmts, function ($node) {
            return $node instanceof ClassMethod;
        });
    }

    public function getMethods(): \Iterator
    {
        $methods = new \ArrayIterator();
        foreach ($this->classMethodNodes as $methodNode) {
            $methods[] = new ReflectionMethod($this->reflector, $this->sourceContext, $methodNode);
        }

        return $methods;
    }

    public function getName(): ClassName
    {
        return $this->sourceContext->resolveClassName($this->classNode->name);
    }
}

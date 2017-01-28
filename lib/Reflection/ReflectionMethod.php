<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\Class_;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassMethod;

class ReflectionMethod
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
    private $methodNode;

    public function __construct(
        Reflector $reflector,
        SourceContext $sourceContext,
        ClassMethod $methodNode
    )
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->methodNode = $methodNode;
    }
}

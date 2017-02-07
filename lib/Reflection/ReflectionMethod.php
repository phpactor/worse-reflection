<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\Class_;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflection\Collection\ReflectionParameterCollection;
use DTL\WorseReflection\Reflection\Collection\ReflectionVariableCollection;

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
     * @var ClassMethod
     */
    private $methodNode;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Visibility
     */
    private $visibility;

    public function __construct(
        Reflector $reflector,
        SourceContext $sourceContext,
        ClassMethod $methodNode
    )
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->methodNode = $methodNode;
        $this->name = $methodNode->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVisibility(): Visibility
    {
        if ($this->methodNode->isProtected()) {
            return Visibility::protected();
        }

        if ($this->methodNode->isPrivate()) {
            return Visibility::private();
        }

        return Visibility::public();
    }

    public function getParameters()
    {
        return new ReflectionParameterCollection($this->reflector, $this->sourceContext, $this->methodNode);
    }

    public function getVariables()
    {
        return new ReflectionVariableCollection($this->reflector, $this->sourceContext, $this->methodNode);
    }
}

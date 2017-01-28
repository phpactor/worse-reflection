<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\Class_;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\Visibility;

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

        $this->setVisibility($methodNode);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    private function setVisibility()
    {
        if ($this->methodNode->isProtected()) {
            $this->visibility = Visibility::protected();
            return;
        }

        if ($this->methodNode->isPrivate()) {
            $this->visibility = Visibility::private();
            return;
        }

        $this->visibility = Visibility::public();
    }
}

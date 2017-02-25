<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\ClassLike;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\ClassName;
use PhpParser\Node\Stmt\Property;
use DTL\WorseReflection\Reflection\Collection\ReflectionMethodCollection;
use DTL\WorseReflection\Reflection\Collection\ReflectionConstantCollection;
use DTL\WorseReflection\Reflection\Collection\ReflectionPropertyCollection;

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
     * @var ClassLike
     */
    private $classNode;

    /*
     * @var ClassMethod[]
     */
    private $classMethodNodes;

    public function __construct(
        Reflector $reflector,
        SourceContext $sourceContext,
        ClassLike $classNode
    )
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->classNode = $classNode;
    }

    public function getMethods(): ReflectionMethodCollection
    {
        return new ReflectionMethodCollection($this->reflector, $this->sourceContext, $this->classNode);
    }

    public function getName(): ClassName
    {
        return $this->sourceContext->resolveClassName(ClassName::fromString($this->classNode->name));
    }

    public function getInterfaces()
    {
        $interfaces = [];
        foreach ($this->classNode->implements as $name) {
            $interfaceName = $this->sourceContext->resolveClassName(ClassName::fromString((string) $name));
            $interfaces[] = $this->reflector->reflectClass($interfaceName);
        }

        return $interfaces;
    }

    public function getConstants(): ReflectionConstantCollection
    {
        return new ReflectionConstantCollection(
            $this->reflector,
            $this->sourceContext,
            $this->classNode
        );
    }

    public function getDocComment(): ReflectionDocComment
    {
        return ReflectionDocComment::fromRaw(array_reduce($this->classNode->getAttribute('comments'), function ($text, $comment) {
            return $text .= $comment->getText();
        }, ''));
    }

    public function hasParentClass(): bool
    {
        return (bool) $this->classNode->extends;
    }

    public function getParentClass(): ReflectionClass
    {
        if (!$this->hasParentClass()) {
            throw new \RuntimeException(sprintf(
                'Class "%s" has no parent',
                $this->getName()->getFqn()
            ));
        }
        $parentName = $this->sourceContext->resolveClassName(ClassName::fromString((string) $this->classNode->extends));
        return $this->reflector->reflectClass($parentName);
    }

    public function getProperties(): ReflectionPropertyCollection
    {
        return new ReflectionPropertyCollection($this->sourceContext, $this->classNode);
    }
}

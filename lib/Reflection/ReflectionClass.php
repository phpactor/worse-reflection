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
use PhpParser\Node\Stmt\Interface_;

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
        return ReflectionMethodCollection::generate($this->reflector, $this, $this->sourceContext, $this->classNode);
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
        $parentName = $this->sourceContext->resolveClassName(ClassName::fromParserName($this->classNode->extends));
        return $this->reflector->reflectClass($parentName);
    }

    public function getProperties(): ReflectionPropertyCollection
    {
        return ReflectionPropertyCollection::fromClassNode($this->sourceContext, $this->classNode);
    }

    public function getVisibleProperties(ReflectionClass $callingClass = null): ReflectionPropertyCollection
    {
        $properties = $this->getPropertiesForCallingClass($this, $callingClass);

        if (false === $this->hasParentClass()) {
            return $properties;
        }

        $class = $this;
        while ($class->hasParentClass()) {
            $parentClass = $class->getParentClass();
            $properties = $this->getPropertiesForCallingClass($parentClass, $callingClass);

            $class = $parentClass;
        }

        return $properties;

    }

    /**
     * TODO: Test me
     */
    public function isSubclassOf(ClassName $className): bool
    {
        if (false === $this->hasParentClass()) {
            return false;
        }

        if ($this->getName() == $className) {
            return true;
        }

        $currentClass = $this;

        while ($currentClass->hasParentClass()) {
            $parentClass = $currentClass->getParentClass();

            if ($parentClass->getName() == $className) {
                return true;
            }

            $currentClass = $parentClass;
        }

        return false;
    }

    public function isInterface(): bool
    {
        return $this->classNode instanceof Interface_;
    }

    private function getPropertiesForCallingClass(ReflectionClass $currentClass, ReflectionClass $callingClass = null)
    {
        if (null === $callingClass) {
            return $currentClass->getProperties()->publicOnly();
        }

        if ($callingClass->getName()->getFqn() === $currentClass->getName()->getFqn()) {
            return $currentClass->getProperties();
        } 

        if ($callingClass->isSubclassOf($currentClass->getName())) {
            return $currentClass->getProperties()->withoutPrivate();
        }

        return $currentClass->getProperties()->publicOnly();
    }
}

<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\ClassLike;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\ClassName;
use PhpParser\Node\Const_;
use PhpParser\Node\Stmt\ClassConst;
use DTL\WorseReflection\Reflection\ReflectionConstant;
use DTL\WorseReflection\Parser\TypeTool;
use DTL\WorseReflection\Reflection\ReflectionDocComment;
use PhpParser\Node\Stmt\Property;

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

    public function getInterfaces()
    {
        $interfaces = [];
        foreach ($this->classNode->implements as $name) {
            $interfaceName = $this->sourceContext->resolveClassName((string) $name);
            $interfaces[] = $this->reflector->reflectClass($interfaceName);
        }

        return $interfaces;
    }

    public function getConstants(): array
    {
        $constants = [];
        foreach ($this->classNode->stmts as $stmt) {
            if (!$stmt instanceof ClassConst) {
                continue;
            }

            foreach ($stmt->consts as $const) {
                $constants[] = new ReflectionConstant($const->name, $const->value->value);
            }
        }

        return $constants;
    }

    public function getDocComment(): ReflectionDocComment
    {
        return ReflectionDocComment::fromRaw(array_reduce($this->classNode->getAttribute('comments'), function ($text, $comment) {
            return $text .= $comment->getText();
        }, ''));
    }

    public function getParentClass(): ReflectionClass
    {
        if (!$this->classNode->extends) {
            throw new \RuntimeException(sprintf(
                'Class "%s" has no parent',
                $this->getName()->getFqn()
            ));
        }
        $parentName = $this->sourceContext->resolveClassName((string) $this->classNode->extends);
        return $this->reflector->reflectClass($parentName);
    }

    public function getProperties(): array
    {
        $properties = [];

        foreach ($this->classNode->stmts as $stmt) {
            if (false === $stmt instanceof Property) {
                continue;
            }

            foreach ($stmt->props as $prop) {
                $properties[] = new ReflectionProperty($stmt, $prop);
            }
        }

        return $properties;
    }
}

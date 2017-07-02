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
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use DTL\WorseReflection\Reflection\AbstractReflectionClass;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\MethodDeclaration;

class ReflectionClass extends AbstractReflectionClass
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ClassLike
     */
    private $node;

    public function __construct(
        Reflector $reflector,
        ClassDeclaration $node
    )
    {
        $this->reflector = $reflector;
        $this->node = $node;
    }

    protected function node(): NamespacedNameInterface
    {
        return $this->node;
    }

    protected function baseClass()
    {
    }

    public function parent()
    {
        if (!$this->node->classBaseClause) {
            return;
        }

        return $this->reflector()->reflectClass(
            ClassName::fromString((string) $this->node->classBaseClause->baseClass->getResolvedName())
        );
    }

    protected function reflector(): Reflector
    {
        return $this->reflector;
    }

    public function properties(): array
    {
    }

    public function methods(): ReflectionMethodCollection
    {
        $methods = array_filter($this->node->classMembers->classMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        return new ReflectionMethodCollection($this->reflector, $methods);
    }

    public function interfaces(): array
    {
        if (!$this->node->classInterfaceClause) {
            return;
        }

        $interfaces = [];

        foreach ($this->node->classInterfaceClause->interfaceNameList->children as $name) {
            if (false === $name instanceof QualifiedName) {
                continue;
            }

            $interface = $this->reflector->reflectClass(
                ClassName::fromString((string) $name->getResolvedName())
            );
            $interfaces[$interface->name()->full()] = $interface;
        }

        return $interfaces;
    }
}

<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\ClassLike;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflection\Collection\ReflectionMethodCollection;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\QualifiedName;
use DTL\WorseReflection\Exception\ClassNotFound;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Reflection\Collection\ReflectionPropertyCollection;

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
    ) {
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

        try {
            return $this->reflector()->reflectClass(
                ClassName::fromString((string) $this->node->classBaseClause->baseClass->getResolvedName())
            );
        } catch (ClassNotFound $e) {
        }
    }

    protected function reflector(): Reflector
    {
        return $this->reflector;
    }

    public function properties(): ReflectionPropertyCollection
    {
        $parentProperties = null;
        if ($this->parent()) {
            $parentProperties = $this->parent()->properties()->byVisibilities([Visibility::public(), Visibility::protected()]);
        }

        $properties = ReflectionPropertyCollection::fromClassDeclaration($this->reflector, $this->node);

        if ($parentProperties) {
            return $parentProperties->merge($properties);
        }

        return $properties;
    }

    public function methods(): ReflectionMethodCollection
    {
        $parentMethods = null;
        if ($this->parent()) {
            $parentMethods = $this->parent()->methods()->byVisibilities([Visibility::public(), Visibility::protected()]);
        }

        $methods = ReflectionMethodCollection::fromClassDeclaration($this->reflector, $this->node);

        if ($parentMethods) {
            return $parentMethods->merge($methods);
        }

        return $methods;
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

            try {
                $interface = $this->reflector->reflectClass(
                    ClassName::fromString((string) $name->getResolvedName())
                );
                $interfaces[$interface->name()->full()] = $interface;
            } catch (ClassNotFound $e) {
            }
        }

        return $interfaces;
    }
}

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
use DTL\WorseReflection\Exception\ClassNotFound;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Reflection\Collection\ReflectionPropertyCollection;
use DTL\WorseReflection\Reflection\Collection\ReflectionInterfaceCollection;

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

    public function constants(): ReflectionConstantCollection
    {
        $parentConstants = null;
        if ($this->parent()) {
            $parentConstants = $this->parent()->constants();
        }

        $constants = ReflectionConstantCollection::fromClassDeclaration($this->reflector, $this->node);

        if ($parentConstants) {
            return $parentConstants->merge($constants);
        }

        return $constants;
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
            $parentProperties = $this->parent()->properties()->byVisibilities([ Visibility::public(), Visibility::protected() ]);
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
            $parentMethods = $this->parent()->methods()->byVisibilities([ Visibility::public(), Visibility::protected() ]);
        }

        $methods = ReflectionMethodCollection::fromClassDeclaration($this->reflector, $this->node);

        if ($parentMethods) {
            return $parentMethods->merge($methods);
        }

        return $methods;
    }

    public function interfaces(): ReflectionInterfaceCollection
    {
        return ReflectionInterfaceCollection::fromClassDeclaration($this->reflector, $this->node);
    }
}

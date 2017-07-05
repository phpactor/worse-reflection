<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use DTL\WorseReflection\Reflection\Collection\ReflectionMethodCollection;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflection\Collection\ReflectionInterfaceCollection;
use DTL\WorseReflection\Visibility;

class ReflectionInterface extends AbstractReflectionClass
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
        InterfaceDeclaration $node
    ) {
        $this->reflector = $reflector;
        $this->node = $node;
    }

    protected function node(): NamespacedNameInterface
    {
        return $this->node;
    }

    protected function reflector(): Reflector
    {
        return $this->reflector;
    }

    public function parents(): ReflectionInterfaceCollection
    {
        return ReflectionInterfaceCollection::fromInterfaceDeclaration($this->reflector, $this->node);
    }

    public function methods(): ReflectionMethodCollection
    {
        $parentMethods = [];
        foreach ($this->parents() as $parent) {
            foreach ($parent->methods()->byVisibilities([ Visibility::public(), Visibility::protected() ]) as $name => $method) {
                $parentMethods[$method->name()] = $method;
            }
        }

        $parentMethods = ReflectionMethodCollection::fromReflectionMethods($this->reflector, $parentMethods);
        $methods = ReflectionMethodCollection::fromInterfaceDeclaration($this->reflector, $this->node);

        return $parentMethods->merge($methods);
    }

}

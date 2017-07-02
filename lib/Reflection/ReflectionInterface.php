<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use PhpParser\Node\Stmt\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use DTL\WorseReflection\Reflection\Collection\ReflectionMethodCollection;

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

    public function parent()
    {
        if (!$this->node->interfaceBaseClause) {
            return;
        }

        try {
            return $this->reflector()->reflectClass(
                ClassName::fromString((string) $this->node->interfaceBaseClause->baseClass->getResolvedName())
            );
        } catch (ClassNotFound $e) {
        }
    }

    public function methods(): ReflectionMethodCollection
    {
        $parentMethods = null;
        if ($this->parent()) {
            $parentMethods = $this->parent()->methods()->byVisibilities([ Visibility::public(), Visibility::protected() ]);
        }

        $methods = ReflectionMethodCollection::fromInterfaceDeclaration($this->reflector, $this->node);

        if ($parentMethods) {
            return $parentMethods->merge($methods);
        }

        return $methods;
    }

}

<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Reflector;
use PhpParser\Node\Stmt\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Visibility;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionConstantCollection;

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

    public function constants(): ReflectionConstantCollection
    {
        $parentConstants = [];
        foreach ($this->parents() as $parent) {
            foreach ($parent->constants() as $constant) {
                $parentConstants[$constant->name()] = $constant;
            }
        }

        $parentConstants = ReflectionConstantCollection::fromReflectionConstants($this->reflector, $parentConstants);
        $constants = ReflectionConstantCollection::fromInterfaceDeclaration($this->reflector(), $this->node);

        return $parentConstants->merge($constants);
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

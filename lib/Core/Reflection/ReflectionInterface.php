<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use PhpParser\Node\Stmt\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\AbstractReflectionClass;

class ReflectionInterface extends AbstractReflectionClass
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var ClassLike
     */
    private $node;

    public function __construct(
        ServiceLocator $serviceLocator,
        InterfaceDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
    }

    protected function node(): Node
    {
        return $this->node;
    }

    public function constants(): ReflectionConstantCollection
    {
        $parentConstants = [];
        foreach ($this->parents() as $parent) {
            foreach ($parent->constants() as $constant) {
                $parentConstants[$constant->name()] = $constant;
            }
        }

        $parentConstants = ReflectionConstantCollection::fromReflectionConstants($this->serviceLocator, $parentConstants);
        $constants = ReflectionConstantCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node);

        return $parentConstants->merge($constants);
    }

    public function parents(): ReflectionInterfaceCollection
    {
        return ReflectionInterfaceCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node);
    }

    public function methods(): ReflectionMethodCollection
    {
        $parentMethods = [];
        foreach ($this->parents() as $parent) {
            foreach ($parent->methods()->byVisibilities([ Visibility::public(), Visibility::protected() ]) as $name => $method) {
                $parentMethods[$method->name()] = $method;
            }
        }

        $parentMethods = ReflectionMethodCollection::fromReflectionMethods($this->serviceLocator, $parentMethods);
        $methods = ReflectionMethodCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node);

        return $parentMethods->merge($methods);
    }

    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }
}

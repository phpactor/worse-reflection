<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Visibility;

class VirtualReflectionClassDecorator extends VirtualReflectionClassLikeDecorator implements ReflectionClass
{
    /**
     * @var ReflectionClass
     */
    private $class;

    /**
     * @var ReflectionMemberProvider[]
     */
    private $memberProviders;

    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    public function __construct(ServiceLocator $serviceLocator, ReflectionClass $class, array $memberProviders = [])
    {
        parent::__construct($class);
        $this->class = $class;
        $this->memberProviders = $memberProviders;
        $this->serviceLocator = $serviceLocator;
    }

    public function isAbstract(): bool
    {
        return $this->class->isAbstract();
    }

    public function constants(): ReflectionConstantCollection
    {
        return $this->class->constants();
    }

    public function parent(): ?ReflectionClass
    {
        return $this->class->parent();
    }

    public function properties(): ReflectionPropertyCollection
    {
        $realProperties = $this->class->properties();
        $virtualProperties = $this->virtualProperties();

        return $realProperties->merge($virtualProperties);
    }

    /**
     * {@inheritDoc}
     */
    public function interfaces(): ReflectionInterfaceCollection
    {
        return $this->class->interfaces();
    }

    public function traits(): ReflectionTraitCollection
    {
        return $this->class->traits();
    }

    public function memberListPosition(): Position
    {
        return $this->class->memberListPosition();
    }

    public function methods(): ReflectionMethodCollection
    {
        $realMethods = $this->class->methods();
        $virtualMethods = $this->virtualMethods();

        return $realMethods->merge($virtualMethods);
    }

    public function members(): ReflectionMemberCollection
    {
        $members = $this->class->members();
        $members->merge($this->virtualMethods());
        return $members;
    }

    private function virtualMethods(ReflectionClass $contextClass = null)
    {
        $virtualMethods = VirtualReflectionMethodCollection::fromReflectionMethods([]);
        if ($parentClass = $this->parent()) {
            assert($parentClass instanceof VirtualReflectionClassDecorator);
            $virtualMethods = $virtualMethods->merge(
                $parentClass->virtualMethods(
                    $contextClass
                )->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        foreach ($this->memberProviders as $memberProvider) {
            $virtualMethods = $virtualMethods->merge(
                $memberProvider->provideMembers($this->serviceLocator, $this->class)->methods()
            );
        }

        return $virtualMethods;
    }

    private function virtualProperties(ReflectionClass $contextClass = null)
    {
        $virtualProperties = VirtualReflectionPropertyCollection::fromReflectionProperties([]);
        if ($parentClass = $this->parent()) {
            assert($parentClass instanceof VirtualReflectionClassDecorator);
            $virtualProperties = $virtualProperties->merge(
                $parentClass->virtualProperties(
                    $contextClass
                )->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        foreach ($this->memberProviders as $memberProvider) {
            $virtualProperties = $virtualProperties->merge(
                $memberProvider->provideMembers($this->serviceLocator, $this->class)->properties()
            );
        }

        return $virtualProperties;
    }

    public function ancestors(): ReflectionClassCollection
    {
        return $this->class->ancestors();
    }

    public function isFinal(): bool
    {
        return $this->class->isFinal();
    }
}

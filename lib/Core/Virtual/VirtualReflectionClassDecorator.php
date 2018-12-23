<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionClassLikeDecorator;

class VirtualReflectionClassDecorator extends VirtualReflectionClassLikeDecorator implements ReflectionClass
{
    /**
     * @var ReflectionClass
     */
    private $class;

    public function __construct(ReflectionClass $class)
    {
        parent::__construct($class);
        $this->class = $class;
    }

    public function isAbstract(): bool
    {
        return $this->class->isAbstract();
    }

    public function constants(): ReflectionConstantCollection
    {
        return $this->class->constants();
    }

    public function parent()
    {
        return $this->class->parent();
    }

    public function properties(): ReflectionPropertyCollection
    {
        return $this->class->properties();
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
}

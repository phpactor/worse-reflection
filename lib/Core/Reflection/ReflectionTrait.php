<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use PhpParser\Node\Stmt\ClassLike;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\AbstractReflectionClass;

class ReflectionTrait extends AbstractReflectionClass
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
        TraitDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
    }

    protected function node(): Node
    {
        return $this->node;
    }

    public function methods(): ReflectionMethodCollection
    {
        return ReflectionMethodCollection::fromTraitDeclaration($this->serviceLocator, $this->node);
    }

    public function properties(): ReflectionPropertyCollection
    {
        $properties = ReflectionPropertyCollection::fromTraitDeclaration($this->serviceLocator, $this->node);

        return $properties;
    }


    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }
}


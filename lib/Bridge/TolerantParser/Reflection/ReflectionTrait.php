<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;

use PhpParser\Node\Stmt\ClassLike;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\AbstractReflectionClass;

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

    /**
     * @var SourceCode
     */
    private $sourceCode;

    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        TraitDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
    }

    protected function node(): Node
    {
        return $this->node;
    }

    public function methods(): ReflectionMethodCollection
    {
        return ReflectionMethodCollection::fromTraitDeclaration($this->serviceLocator, $this->node, $this);
    }

    public function properties(): ReflectionPropertyCollection
    {
        $properties = ReflectionPropertyCollection::fromTraitDeclaration($this->serviceLocator, $this->node, $this);

        return $properties;
    }


    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }

    public function sourceCode(): SourceCode
    {
        return $this->sourceCode;
    }
}


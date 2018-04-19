<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use PhpParser\Node\Stmt\ClassLike;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait as CoreReflectionTrait;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionMemberCollection as TolerantReflectionMemberCollection;

class ReflectionTrait extends AbstractReflectionClass implements CoreReflectionTrait
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var TraitDeclaration
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

    /**
     * @return TraitDeclaration
     */
    protected function node(): Node
    {
        return $this->node;
    }

    public function methods(ReflectionClassLike $contextClass = null): CoreReflectionMethodCollection
    {
        $contextClass = $contextClass ?: $this;
        return ReflectionMethodCollection::fromTraitDeclaration($this->serviceLocator, $this->node, $contextClass);
    }

    public function members(): ReflectionMemberCollection
    {
        $members = TolerantReflectionMemberCollection::empty();
        $members = $members->merge($this->properties());
        $members = $members->merge($this->methods());

        return $members;
    }

    public function properties(): CoreReflectionPropertyCollection
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

    public function isInstanceOf(ClassName $className): bool
    {
        if ($className == $this->name()) {
            return true;
        }

        return false;
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator->docblockFactory()->create($this->node()->getLeadingCommentAndWhitespaceText());
    }
}

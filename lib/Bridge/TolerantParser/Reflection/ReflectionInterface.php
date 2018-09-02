<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionMethodCollection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection as CoreReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection as CoreReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface as CoreReflectionInterface;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;

class ReflectionInterface extends AbstractReflectionClass implements CoreReflectionInterface
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var InterfaceDeclaration
     */
    private $node;

    /**
     * @var SourceCode
     */
    private $sourceCode;
    private $parents;
    private $methods;

    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        InterfaceDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
    }

    /**
     * @return InterfaceDeclaration
     */
    protected function node(): Node
    {
        return $this->node;
    }

    public function members(): ReflectionMemberCollection
    {
        return ChainReflectionMemberCollection::fromCollections([
            $this->constants(),
            $this->methods()
        ]);
    }

    public function constants(): CoreReflectionConstantCollection
    {
        $parentConstants = [];
        foreach ($this->parents() as $parent) {
            foreach ($parent->constants() as $constant) {
                $parentConstants[$constant->name()] = $constant;
            }
        }

        $parentConstants = ReflectionConstantCollection::fromReflectionConstants($this->serviceLocator, $parentConstants);
        $constants = ReflectionConstantCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node, $this);

        return $parentConstants->merge($constants);
    }

    public function parents(): CoreReflectionInterfaceCollection
    {
        if ($this->parents) {
            return $this->parents;
        }

        $this->parents = ReflectionInterfaceCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node);

        return $this->parents;
    }

    public function isInstanceOf(ClassName $className): bool
    {
        if ($className == $this->name()) {
            return true;
        }

        if ($this->parents()) {
            foreach ($this->parents() as $parent) {
                if ($parent->isInstanceOf($className)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function methods(ReflectionInterface $context = null): CoreReflectionMethodCollection
    {
        if ($this->methods) {
            return $this->methods;
        }

        $parentMethods = [];
        foreach ($this->parents() as $parent) {
            foreach ($parent->methods($this)->byVisibilities([ Visibility::public(), Visibility::protected() ]) as $name => $method) {
                $parentMethods[$method->name()] = $method;
            }
        }

        $context = $context ?: $this;
        $parentMethods = ReflectionMethodCollection::fromReflectionMethods($this->serviceLocator, $parentMethods);
        $methods = ReflectionMethodCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node, $context);

        $this->methods =  $parentMethods->merge($methods);

        return $this->methods;
    }

    public function inferredMethods(CoreReflectionClass $contextClass = null): CoreReflectionMethodCollection
    {
        $actualMethods = $this->methods($contextClass);
        $virtualMethods = $this->docblock()->methods($contextClass ?: $this);

        return $actualMethods->merge($virtualMethods);
    }

    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }

    public function sourceCode(): SourceCode
    {
        return $this->sourceCode;
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator->docblockFactory()->create($this->node()->getLeadingCommentAndWhitespaceText());
    }
}

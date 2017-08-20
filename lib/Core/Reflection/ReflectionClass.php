<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\TokenKind;
use PhpParser\Node\Stmt\ClassLike;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Visibility;

class ReflectionClass extends AbstractReflectionClass
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
        ClassDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
    }

    protected function node(): Node
    {
        return $this->node;
    }

    public function isAbstract(): bool
    {
        $modifier = $this->node->abstractOrFinalModifier;

        if (!$modifier) {
            return false;
        }

        return $modifier->kind === TokenKind::AbstractKeyword;
    }

    public function constants(): ReflectionConstantCollection
    {
        $parentConstants = null;
        if ($this->parent()) {
            $parentConstants = $this->parent()->constants();
        }

        $constants = ReflectionConstantCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentConstants) {
            return $parentConstants->merge($constants);
        }

        return $constants;
    }

    public function parent()
    {
        if (!$this->node->classBaseClause) {
            return;
        }

        try {
            $reflectedClass = $this->serviceLocator->reflector()->reflectClass(
                ClassName::fromString((string) $this->node->classBaseClause->baseClass->getResolvedName())
            );

            if (!$reflectedClass instanceof ReflectionClass) {
                $this->serviceLocator->logger()->warning(sprintf(
                    'Class cannot extend interface. Class "%s" extends interface "%s"',
                    $this->name(),
                    $reflectedClass->name()
                ));
                return;
            }

            return $reflectedClass;
        } catch (ClassNotFound $e) {
        }
    }

    public function properties(): ReflectionPropertyCollection
    {
        $properties = ReflectionPropertyCollection::empty($this->serviceLocator);

        if ($this->traits()->count() > 0) {
            foreach ($this->traits() as $trait) {
                $properties = $properties->merge($trait->properties());
            }
        }

        if ($this->parent()) {
            $properties = $properties->merge(
                $this->parent()->properties()->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        $properties = $properties->merge(ReflectionPropertyCollection::fromClassDeclaration($this->serviceLocator, $this->node));

        return $properties;
    }

    public function methods(): ReflectionMethodCollection
    {
        $methods = ReflectionMethodCollection::empty($this->serviceLocator);

        if ($this->traits()->count() > 0) {
            foreach ($this->traits() as $trait) {
                $methods = $methods->merge($trait->methods());
            }
        }

        if ($this->parent()) {
            $methods = $methods->merge(
                $this->parent()->methods()->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        $methods = $methods->merge(ReflectionMethodCollection::fromClassDeclaration($this->serviceLocator, $this->node));

        return $methods;
    }

    public function interfaces(): ReflectionInterfaceCollection
    {
        $parentInterfaces = null;
        if ($this->parent()) {
            $parentInterfaces = $this->parent()->interfaces();
        }

        $interfaces = ReflectionInterfaceCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentInterfaces) {
            return $parentInterfaces->merge($interfaces);
        }

        return $interfaces;
    }

    public function traits(): ReflectionTraitCollection
    {
        $parentTraits = null;
        if ($this->parent()) {
            $parentTraits = $this->parent()->traits();
        }

        $traits = ReflectionTraitCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentTraits) {
            return $parentTraits->merge($traits);
        }

        return $traits;
    }


    public function memberListPosition(): Position
    {
        return Position::fromFullStartStartAndEnd(
            $this->node->classMembers->openBrace->fullStart,
            $this->node->classMembers->openBrace->start,
            $this->node->classMembers->openBrace->start + $this->node->classMembers->openBrace->length
        );
    }

    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }

    public function isInstanceOf(ClassName $className): bool
    {
        if ($className == $this->name()) {
            return true;
        }

        if ($this->parent()) {
            return $this->parent()->isInstanceOf($className);
        }

        return $this->interfaces()->has((string) $className);
    }
}

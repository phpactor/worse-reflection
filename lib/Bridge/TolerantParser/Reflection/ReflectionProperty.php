<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver\DeclaredMemberTypeResolver;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty as CoreReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\TypeResolver\PropertyTypeResolver;
use Phpactor\WorseReflection\Core\Types;
use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Core\Type;

class ReflectionProperty extends AbstractReflectionClassMember implements CoreReflectionProperty
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var PropertyDeclaration
     */
    private $propertyDeclaration;

    /**
     * @var Variable
     */
    private $variable;

    /**
     * @var ReflectionClassLike
     */
    private $class;

    /**
     * @var PropertyTypeResolver
     */
    private $typeResolver;

    /**
     * @var DeclaredMemberTypeResolver
     */
    private $memberTypeResolver;

    public function __construct(
        ServiceLocator $serviceLocator,
        AbstractReflectionClass $class,
        PropertyDeclaration $propertyDeclaration,
        Variable $variable
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->propertyDeclaration = $propertyDeclaration;
        $this->variable = $variable;
        $this->class = $class;
        $this->typeResolver = new PropertyTypeResolver($this, $this->serviceLocator->logger());
        $this->memberTypeResolver = new DeclaredMemberTypeResolver();
    }

    public function declaringClass(): ReflectionClassLike
    {
        /** @var NamespacedNameInterface $classDeclaration */
        $classDeclaration = $this->propertyDeclaration->getFirstAncestor(ClassDeclaration::class, TraitDeclaration::class);
        $class = $classDeclaration->getNamespacedName();

        if (null === $class) {
            throw new \InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for method "%s"',
                $this->name()
            ));
        }

        return $this->serviceLocator->reflector()->reflectClassLike(ClassName::fromString($class));
    }

    public function name(): string
    {
        return (string) $this->variable->getName();
    }

    public function inferredTypes(): Types
    {
        return $this->typeResolver->resolve();
    }

    public function type(): Type
    {
        return $this->memberTypeResolver->resolve(
            $this->propertyDeclaration,
            $this->propertyDeclaration->typeDeclaration,
            $this->class()->name()
        );
    }

    protected function node(): Node
    {
        return $this->propertyDeclaration;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }

    public function class(): ReflectionClassLike
    {
        return $this->class;
    }

    public function isStatic(): bool
    {
        return $this->propertyDeclaration->isStatic();
    }

    public function isVirtual(): bool
    {
        return false;
    }
}

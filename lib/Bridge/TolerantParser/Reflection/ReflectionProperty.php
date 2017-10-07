<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Visibility;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\TokenKind;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty as CoreReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

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
     * @var AbstractReflectionClass
     */
    private $class;

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
    }

    public function declaringClass(): ReflectionClassLike
    {
        $class = $this->propertyDeclaration->getFirstAncestor(ClassDeclaration::class, TraitDeclaration::class)->getNamespacedName();

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

    public function visibility(): Visibility
    {
        foreach ($this->propertyDeclaration->modifiers as $token) {
            if ($token->kind === TokenKind::PrivateKeyword) {
                return Visibility::private();
            }

            if ($token->kind === TokenKind::ProtectedKeyword) {
                return Visibility::protected();
            }
        }

        return Visibility::public();
    }

    public function type(): Type
    {
        return $this->serviceLocator->docblockResolver()->propertyType($this->propertyDeclaration);
    }

    public function isStatic(): bool
    {
        return $this->propertyDeclaration->isStatic();
    }

    protected function node(): Node
    {
        return $this->variable;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }

    public function class(): ReflectionClassLike
    {
        return $this->class;
    }
}

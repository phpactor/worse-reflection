<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Visibility;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\ServiceLocator;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\DocblockResolver;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Type;

class ReflectionProperty extends AbstractReflectedNode
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

    public function __construct(ServiceLocator $serviceLocator, PropertyDeclaration $propertyDeclaration, Variable $variable)
    {
        $this->serviceLocator = $serviceLocator;
        $this->propertyDeclaration = $propertyDeclaration;
        $this->variable = $variable;
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
}


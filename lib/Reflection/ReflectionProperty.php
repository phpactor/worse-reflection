<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Visibility;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Reflector;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\DocblockResolver;

class ReflectionProperty
{
    private $reflector;
    private $propertyDeclaration;
    private $variable;
    private $docblockResolver;

    public function __construct(Reflector $reflector, PropertyDeclaration $propertyDeclaration, Variable $variable)
    {
        $this->reflector = $reflector;
        $this->propertyDeclaration = $propertyDeclaration;
        $this->variable = $variable;
        $this->docblockResolver = new DocblockResolver($reflector);
    }

    public function name()
    {
        return (string) $this->variable->getName();
    }

    public function visibility()
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

    public function type()
    {
        return $this->docblockResolver->propertyType($this->propertyDeclaration);
    }
}

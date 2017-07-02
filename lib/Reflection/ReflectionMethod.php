<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Type;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\TokenKind;

class ReflectionMethod
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ClassMethod
     */
    private $node;

    /**
     * @var Visibility
     */
    private $visibility;

    public function __construct(
        Reflector $reflector,
        MethodDeclaration $node
    )
    {
        $this->reflector = $reflector;
        $this->node = $node;
    }

    public function name(): string
    {
        return $this->node->getName();
    }

    public function visibility(): Visibility
    {
        foreach ($this->node->modifiers as $token) {
            if ($token->kind === TokenKind::PrivateKeyword) {
                return Visibility::private();
            }

            if ($token->kind === TokenKind::ProtectedKeyword) {
                return Visibility::protected();
            }
        }

        return Visibility::public();
    }

    public function getParameters()
    {
        return new ReflectionParameterCollection($this->reflector, $this->sourceContext, $this->node);
    }

    public function getReturnType(): Type
    {
        return Type::fromString($this->sourceContext, (string) $this->node->returnType);
    }

    public function getVariables()
    {
        return new ReflectionVariableCollection(
            $this->reflector,
            $this->sourceContext,
            $this->node
        );
    }
}

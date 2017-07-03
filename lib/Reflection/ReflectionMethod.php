<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Type;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\TokenKind;
use Microsoft\PhpParser\Token;
use DTL\WorseReflection\DocblockResolver;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use DTL\WorseReflection\ClassName;

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

    /**
     * @var DocblockResolver
     */
    private $docblockResolver;

    public function __construct(
        Reflector $reflector,
        MethodDeclaration $node
    ) {
        $this->reflector = $reflector;
        $this->node = $node;
        $this->docblockResolver = new DocblockResolver($reflector);
    }

    public function name(): string
    {
        return $this->node->getName();
    }

    public function class(): ReflectionClass
     {
     $class = $this->node->getFirstAncestor(ClassDeclaration::class)->getNamespacedName();

     return $this->reflector->reflectClass(ClassName::fromString($class));
     }

    public function isAbstract(): bool
    {
        foreach ($this->node->modifiers as $token) {
            if ($token->kind === TokenKind::AbstractKeyword) {
                return true;
            }
        }

        return false;
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

    public function type(): Type
    {
        if (!$this->node->returnType) {
            return $this->docblockResolver->methodReturnTypeFromNodeDocblock($this->class(), $this->node);
        }

        if ($this->node->returnType instanceof Token) {
            return Type::fromString($this->node->returnType->getText($this->node->getFileContents()));
        }

        return Type::fromString($this->node->returnType->getResolvedName());
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

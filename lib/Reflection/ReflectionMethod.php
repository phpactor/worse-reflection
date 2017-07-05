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
use DTL\WorseReflection\Reflection\Collection\ReflectionParameterCollection;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use DTL\WorseReflection\Reflection\AbstractReflectionClass;
use DTL\WorseReflection\Reflection\ReflectionDocblock;

final class ReflectionMethod
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

    private $type;

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

    public function class(): AbstractReflectionClass
 {
     $class = $this->node->getFirstAncestor(ClassDeclaration::class, InterfaceDeclaration::class)->getNamespacedName();

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

    public function parameters(): ReflectionParameterCollection
    {
        return ReflectionParameterCollection::fromMethodDeclaration($this->reflector, $this->node);
    }

    public function docblock(): ReflectionDocblock
    {
        return ReflectionDocblock::fromNode($this->node);
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

        if ($this->type) {
            return $this->type;
        }

        if (!$this->node->returnType) {
            return $this->type = $this->docblockResolver->methodReturnTypeFromNodeDocblock($this->class(), $this->node);
        }

        // scalar
        if ($this->node->returnType instanceof Token) {
            return $this->type = Type::fromString($this->node->returnType->getText($this->node->getFileContents()));
        }

        $this->type = Type::fromString($this->node->returnType->getResolvedName());

        return $this->type;
    }
}

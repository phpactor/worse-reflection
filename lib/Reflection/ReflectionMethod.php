<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Visibility;
use Phpactor\WorseReflection\Type;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\TokenKind;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\DocblockResolver;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionParameterCollection;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Reflection\AbstractReflectionClass;
use Phpactor\WorseReflection\Reflection\ReflectionDocblock;
use Microsoft\PhpParser\Node;

final class ReflectionMethod extends AbstractReflectedNode
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
        if (!$this->node->returnType) {
            return $this->docblockResolver->methodReturnTypeFromNodeDocblock($this->class(), $this->node);
        }

        // scalar
        if ($this->node->returnType instanceof Token) {
            return Type::fromString($this->node->returnType->getText($this->node->getFileContents()));
        }

        return Type::fromString($this->node->returnType->getResolvedName());
    }

    protected function node(): Node
    {
        return $this->node;
    }
}

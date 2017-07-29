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
use Phpactor\WorseReflection\Docblock;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Reflection\Formatted\MethodHeader;
use Phpactor\WorseReflection\Reflection\Inference\FrameBuilder;
use Phpactor\WorseReflection\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Reflection\Inference\Frame;

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

    /**
     * @var FrameBuilder
     */
    private $frameBuilder;

    public function __construct(
        Reflector $reflector,
        MethodDeclaration $node
    ) {
        $this->reflector = $reflector;
        $this->node = $node;
        $this->docblockResolver = new DocblockResolver($reflector);
        $this->frameBuilder = new FrameBuilder(new NodeValueResolver($reflector));
    }

    public function name(): string
    {
        return $this->node->getName();
    }

    public function frame(): Frame
    {
        return $this->frameBuilder->buildFromNode($this->node);
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

    public function isStatic(): bool
    {
        return $this->node->isStatic();
    }

    public function parameters(): ReflectionParameterCollection
    {
        return ReflectionParameterCollection::fromMethodDeclaration($this->reflector, $this->node);
    }

    public function docblock(): Docblock
    {
        return Docblock::fromNode($this->node);
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

    /**
     * If type not explicitly set, try and infer it from the docblock.
     */
    public function inferredReturnType(): Type
    {
        if (!$this->node->returnType) {
            return $this->docblockResolver->methodReturnTypeFromNodeDocblock($this->class(), $this->node);
        }

        return $this->returnType();
    }

    public function returnType(): Type
    {
        if (null === $this->node->returnType) {
            return Type::undefined();
        }

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

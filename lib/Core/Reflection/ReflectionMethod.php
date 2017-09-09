<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\TokenKind;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\DocblockResolver;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Core\Docblock;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Reflection\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Reflection\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\ClassLike;

class ReflectionMethod extends AbstractReflectionClassMember
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

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

    /**
     * @var AbstractReflectionClass
     */
    private $class;

    public function __construct(
        ServiceLocator $serviceLocator,
        AbstractReflectionClass $class,
        MethodDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->class = $class;
    }

    public function name(): string
    {
        return $this->node->getName();
    }

    public function frame(): Frame
    {
        return $this->serviceLocator->frameBuilder()->buildFromNode($this->node);
    }

    public function declaringClass(): AbstractReflectionClass
    {
        $class = $this->node->getFirstAncestor(ClassLike::class)->getNamespacedName();

        if (null === $class) {
            throw new \InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for method "%s"',
                $this->name()
            ));
        }

        return $this->serviceLocator->reflector()->reflectClassLike(ClassName::fromString($class));
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
        return ReflectionParameterCollection::fromMethodDeclaration($this->serviceLocator, $this->node);
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
        $type = $this->serviceLocator->docblockResolver()->methodReturnTypeFromNodeDocblock($this->class(), $this->node);
        if (Type::unknown() != $type) {
            return $type;
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

    public function body(): NodeText
    {
        $statements = $this->node->compoundStatementOrSemicolon->statements;
        return NodeText::fromString(implode(PHP_EOL, array_reduce($statements, function ($acc, $statement) {
            $acc[] = (string) $statement->getText();
            return $acc;
        }, [])));
    }

    public function class(): AbstractReflectionClass
    {
        return $this->class;
    }

    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}


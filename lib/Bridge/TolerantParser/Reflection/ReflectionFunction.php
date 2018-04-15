<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction as CoreReflectionFunction;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\NodeText;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver\DeclaredMemberTypeResolver;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Reflection\TypeResolver\FunctionReturnTypeResolver;

class ReflectionFunction extends AbstractReflectedNode implements CoreReflectionFunction
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var FunctionDeclaration
     */
    private $node;

    public function __construct(ServiceLocator $serviceLocator, FunctionDeclaration $node)
    {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
    }

    public function name(): Name
    {
        return Name::fromParts($this->node->getNamespacedName()->getNameParts());
    }

    public function frame(): Frame
    {
        return $this->serviceLocator->frameBuilder()->build($this->node());
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator->docblockFactory()->create($this->node()->getLeadingCommentAndWhitespaceText());
    }

    public function inferredTypes(): Types
    {
        return (new FunctionReturnTypeResolver($this))->resolve();
    }

    public function type(): Type
    {
        $type = $this->node->returnType;

        if (null === $type) {
            return Type::unknown();
        }

        if ($type instanceof Token) {
            return Type::fromString($type->getText($this->node->getFileContents()));
        }

        if (!$type instanceof QualifiedName) {
            return Type::unknown();
        }

        return Type::fromString($type->getResolvedName());
    }

    public function parameters(): ReflectionParameterCollection
    {
    }

    public function body(): NodeText
    {
    }

    protected function node(): Node
    {
        return $this->node;
    }
}

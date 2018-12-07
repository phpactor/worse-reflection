<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\IncludeWalker;
use Phpactor\WorseReflection\Core\Logger;
use RuntimeException;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Expression\ListIntrinsicExpression;
use Microsoft\PhpParser\Node\ArrayElement;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\Statement\ForeachStatement;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Microsoft\PhpParser\Node\ForeachValue;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\VariableWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\AssignmentWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\CatchWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\ForeachWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\FunctionLikeWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\InstanceOfWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\AssertFrameWalker;

final class FrameBuilder
{
    /**
     e @var SymbolContextResolver
     */
    private $symbolContextResolver;

    /**
     * @var FrameWalker[]
     */
    private $walkers;

    public static function create(DocBlockFactory $docblockFactory, SymbolContextResolver $symbolContextResolver, Logger $logger, array $walkers = [])
    {
        $nameResolver = new FullyQualifiedNameResolver($logger);
        $walkers = array_merge([
            new AssertFrameWalker(),
            new FunctionLikeWalker(),
            new VariableWalker($docblockFactory, $nameResolver),
            new AssignmentWalker($logger),
            new CatchWalker(),
            new ForeachWalker(),
            new InstanceOfWalker(),
            new IncludeWalker($logger),
        ], $walkers);

        return new self($symbolContextResolver, $walkers);
    }

    /**
     * @param FrameWalker[] $walkers
     */
    public function __construct(SymbolContextResolver $symbolContextResolver, array $walkers)
    {
        $this->symbolContextResolver = $symbolContextResolver;
        $this->walkers = $walkers;
    }

    public function build(Node $node): Frame
    {
        return $this->walkNode($this->resolveScopeNode($node), $node);
    }

    private function walkNode(Node $node, Node $targetNode, Frame $frame = null)
    {
        if ($frame === null) {
            $frame = new Frame($node->getNodeKindName());
        }

        if (null === $frame) {
            throw new RuntimeException(
                'Walk node was not intiated with a SouceFileNode, this should never happen.'
            );
        }

        foreach ($this->walkers as $walker) {
            if ($walker->canWalk($node)) {
                $frame = $walker->walk($this, $frame, $node);
            }
        }

        foreach ($node->getChildNodes() as $childNode) {
            if ($found = $this->walkNode($childNode, $targetNode, $frame)) {
                return $found;
            }
        }

        // if we found what we were looking for then return it
        if ($node === $targetNode) {
            return $frame;
        }

        // we start with the source node and we finish with the source node.
        if ($node instanceof SourceFileNode) {
            return $frame;
        }
    }


    /**
     * @internal For use with walkers
     *
     * TODO: Make an interface for this, extract it.
     */
    public function resolveNode(Frame $frame, $node): SymbolContext
    {
        $info = $this->symbolContextResolver->resolveNode($frame, $node);

        if ($info->issues()) {
            $frame->problems()->add($info);
        }

        return $info;
    }

    private function resolveScopeNode(Node $node): Node
    {
        if ($node instanceof SourceFileNode) {
            return $node;
        }

        // do not traverse the whole source file for functions
        if ($node instanceof FunctionLike) {
            return $node;
        }

        $scopeNode = $node->getFirstAncestor(AnonymousFunctionCreationExpression::class, FunctionLike::class, SourceFileNode::class);

        if (null === $scopeNode) {
            throw new RuntimeException(sprintf(
                'Could not find scope node for "%s", this should not happen.',
                get_class($node)
            ));
        }

        // if this is an anonymous functoin, traverse the parent scope to
        // resolve any potential variable imports.
        if ($scopeNode instanceof AnonymousFunctionCreationExpression) {
            return $this->resolveScopeNode($scopeNode->parent);
        }

        return $scopeNode;
    }
}

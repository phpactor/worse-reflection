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

final class FrameBuilder
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     e @var SymbolContextResolver
     */
    private $symbolContextResolver;

    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    /**
     * @var FullyQualifiedNameResolver
     */
    private $nameResolver;

    /**
     * @var FrameWalker[]
     */
    private $walkers;

    /**
     * @var DocBlockFactory
     */
    private $docblockFactory;

    public function __construct(DocBlockFactory $docblockFactory, SymbolContextResolver $symbolContextResolver, Logger $logger)
    {
        $this->logger = $logger;
        $this->symbolContextResolver = $symbolContextResolver;
        $this->symbolFactory = new SymbolFactory();
        $this->nameResolver = new FullyQualifiedNameResolver($logger);
        $this->docblockFactory = $docblockFactory;
        $this->walkers = [
            new VariableWalker($this->symbolFactory, $this->docblockFactory, $this->nameResolver),
            new AssignmentWalker($this->symbolFactory, $this->logger),
            new CatchWalker($this->symbolFactory)
        ];
    }

    public function build(Node $node): Frame
    {
        return $this->walkNode($this->resolveScopeNode($node), $node);
    }

    private function walkNode(Node $node, Node $targetNode, Frame $frame = null)
    {
        if ($node instanceof SourceFileNode) {
            $frame = new Frame($node->getNodeKindName());
        }

        if (null === $frame) {
            throw new RuntimeException(
                'Walk node was not intiated with a SouceFileNode, this should never happen.'
            );
        }

        if ($node instanceof FunctionLike) {
            assert($node instanceof Node);
            $frame = $frame->new($node->getNodeKindName() . '#' . $this->functionName($node));
            $this->walkFunctionLike($frame, $node);
        }

        foreach ($this->walkers as $walker) {
            if ($walker->canWalk($node)) {
                $walker->walk($this, $frame, $node);
            }
        }

        if ($node instanceof ForeachStatement) {
            $this->walkForeachStatement($frame, $node);
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
     * @param FunctionDeclaration|AnonymousFunctionCreationExpression $node
     */
    private function walkFunctionLike(Frame $frame, FunctionLike $node)
    {
        $namespace = $node->getNamespaceDefinition();
        $classNode = $node->getFirstAncestor(
            ClassDeclaration::class,
            InterfaceDeclaration::class,
            TraitDeclaration::class
        );

        // works for both closure and class method (we currently ignore binding)
        if ($classNode) {
            $classType = $this->resolveNode($frame, $classNode)->type();
            $context = $this->symbolFactory->context(
                'this',
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'type' => $classType,
                    'symbol_type' => Symbol::VARIABLE,
                ]
            );

            // add this and self
            // TODO: self is NOT added here - does it work?
            $frame->locals()->add(Variable::fromSymbolContext($context));
        }

        if ($node instanceof AnonymousFunctionCreationExpression) {
            $this->addAnonymousImports($frame, $node);
        }

        if (null === $node->parameters) {
            return;
        }

        /** @var Parameter $parameterNode */
        foreach ($node->parameters->getElements() as $parameterNode) {
            $parameterName = $parameterNode->variableName->getText($node->getFileContents());

            $symbolContext = $this->resolveNode($frame, $parameterNode);

            $context = $this->symbolFactory->context(
                $parameterName,
                $parameterNode->getStart(),
                $parameterNode->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                    'type' => $symbolContext->types()->best(),
                    'value' => $symbolContext->value(),
                ]
            );

            $frame->locals()->add(Variable::fromSymbolContext($context));
        }
    }

    private function addAnonymousImports(Frame $frame, AnonymousFunctionCreationExpression $node)
    {
        $useClause = $node->anonymousFunctionUseClause;

        if (null === $useClause) {
            return;
        }

        $parentFrame = $frame->parent();
        $parentVars = $parentFrame->locals()->lessThanOrEqualTo($node->getStart());

        foreach ($useClause->useVariableNameList->getElements() as $element) {
            $varName = $element->variableName->getText($node->getFileContents());

            $variableContext = $this->symbolFactory->context(
                $varName,
                $element->getStart(),
                $element->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                ]
            );
            $varName = $variableContext->symbol()->name();

            // if not in parent scope, then we know nothing about it
            // add it with above context and continue
            // TODO: Do we infer the type hint??
            if (0 === $parentVars->byName($varName)->count()) {
                $frame->locals()->add(Variable::fromSymbolContext($variableContext));
                continue;
            }

            $variable = $parentVars->byName($varName)->last();

            $variableContext = $variableContext
                ->withType($variable->symbolContext()->type())
                ->withValue($variable->symbolContext()->value());

            $frame->locals()->add(Variable::fromSymbolContext($variableContext));
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

        $scopeNode = $node->getFirstAncestor(SourceFileNode::class);

        if (null === $scopeNode) {
            throw new RuntimeException(sprintf(
                'Could not find scope node for "%s", this should not happen.',
                get_class($node)
            ));
        }

        return $scopeNode;
    }

    private function functionName(FunctionLike $node)
    {
        if ($node instanceof MethodDeclaration) {
            return $node->getName();
        }

        if ($node instanceof FunctionDeclaration) {
            return array_reduce($node->getNameParts(), function ($accumulator, Token $part) {
                return $accumulator
                    . '\\' .
                    $part->getText();
            }, '');
        }

        if ($node instanceof AnonymousFunctionCreationExpression) {
            return '<anonymous>';
        }

        return '<unknown>';
    }

    private function walkForeachStatement(Frame $frame, ForeachStatement $node)
    {
        $collection = $this->resolveNode($frame, $node->forEachCollectionName);
        $itemName = $node->foreachValue;

        if (!$itemName instanceof ForeachValue) {
            return;
        }

        if (!$itemName->expression instanceof ParserVariable) {
            return;
        }

        $itemName = $itemName->expression->name->getText($node->getFileContents());

        $collectionType = $collection->types()->best();

        $context = $this->symbolFactory->context(
            $itemName,
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        if ($collectionType->arrayType()->isDefined()) {
            $context = $context->withType($collectionType->arrayType());
        }

        $frame->locals()->add(Variable::fromSymbolContext($context));
    }
}

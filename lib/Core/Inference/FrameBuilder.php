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
     * @var array
     */
    private $injectedTypes = [];

    /**
     * @var FullyQualifiedNameResolver
     */
    private $nameResolver;

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

        $this->injectVariablesFromComment($frame, $node);

        if ($node instanceof ParserVariable) {
            $this->walkVariable($frame, $node);
        }

        if ($node instanceof AssignmentExpression) {
            $this->walkAssignment($frame, $node);
        }

        if ($node instanceof CatchClause) {
            $this->walkExceptionCatch($frame, $node);
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

    private function walkExceptionCatch(Frame $frame, CatchClause $node)
    {
        if (!$node->qualifiedName) {
            return;
        }

        $typeContext = $this->resolveNode($frame, $node->qualifiedName);
        $context = $this->symbolFactory->context(
            $node->variableName->getText($node->getFileContents()),
            $node->variableName->getStartPosition(),
            $node->variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $typeContext->type(),
            ]
        );

        $frame->locals()->add(Variable::fromSymbolContext($context));
    }

    private function walkAssignment(Frame $frame, AssignmentExpression $node)
    {
        $rightContext = $this->resolveNode($frame, $node->rightOperand);

        if ($node->leftOperand instanceof ParserVariable) {
            return $this->walkParserVariable($frame, $node->leftOperand, $rightContext);
        }

        if ($node->leftOperand instanceof ListIntrinsicExpression) {
            return $this->walkList($frame, $node->leftOperand, $rightContext);
        }

        if ($node->leftOperand instanceof MemberAccessExpression) {
            return $this->walkMemberAccessExpression($frame, $node->leftOperand, $rightContext);
        }

        if ($node->leftOperand instanceof SubscriptExpression) {
            return $this->walkSubscriptExpression($frame, $node->leftOperand, $rightContext);
        }


        $this->logger->warning(sprintf(
            'Do not know how to assign to left operand "%s"',
            get_class($node->leftOperand)
        ));
    }

    private function walkParserVariable(Frame $frame, ParserVariable $leftOperand, SymbolContext $rightContext)
    {
        $name = $leftOperand->name->getText($leftOperand->getFileContents());
        $context = $this->symbolFactory->context(
            $name,
            $leftOperand->getStart(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $rightContext->type(),
                'value' => $rightContext->value(),
            ]
        );

        $frame->locals()->add(Variable::fromSymbolContext($context));
    }

    private function walkMemberAccessExpression(Frame $frame, MemberAccessExpression $leftOperand, SymbolContext $typeContext)
    {
        $variable = $leftOperand->dereferencableExpression;

        // we do not track assignments to other classes.
        if (false === in_array($variable, [ '$this', 'self' ])) {
            return;
        }

        $memberNameNode = $leftOperand->memberName;

        // TODO: Sort out this mess.
        //       If the node is not a token (e.g. it is a variable) then
        //       evaluate the variable (e.g. $this->$foobar);
        if ($memberNameNode instanceof Token) {
            $memberName = $memberNameNode->getText($leftOperand->getFileContents());
        } else {
            $memberNameInfo = $this->resolveNode($frame, $memberNameNode);

            if (false === is_string($memberNameInfo->value())) {
                return;
            }

            $memberName = $memberNameInfo->value();
        }

        $context = $this->symbolFactory->context(
            $memberName,
            $leftOperand->getStart(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $typeContext->type(),
                'value' => $typeContext->value(),
            ]
        );

        $frame->properties()->add(Variable::fromSymbolContext($context));
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

    private function injectVariablesFromComment(Frame $frame, Node $node)
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();
        $docblock = $this->docblockFactory->create($comment);

        if (false === $docblock->isDefined()) {
            return;
        }

        $vars = $docblock->vars();

        /** @var DocBlockVar $var */
        foreach ($docblock->vars() as $var) {
            $this->injectedTypes[ltrim($var->name(), '$')] = $this->nameResolver->resolve(
                $node,
                $var->types()->best()
            );
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

    private function walkVariable(Frame $frame, ParserVariable $node)
    {
        if (false === $node->name instanceof Token) {
            return;
        }

        $context = $this->symbolFactory->context(
            $node->name->getText($node->getFileContents()),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        $symbolName = $context->symbol()->name();

        if (false === isset($this->injectedTypes[$symbolName])) {
            return;
        }

        $context = $context->withType($this->injectedTypes[$symbolName]);
        $frame->locals()->add(Variable::fromSymbolContext($context));
        unset($this->injectedTypes[$symbolName]);
    }

    private function resolveNode(Frame $frame, $node): SymbolContext
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

    private function walkList(Frame $frame, ListIntrinsicExpression $leftOperand, SymbolContext $symbolContext)
    {
        $value = $symbolContext->value();

        foreach ($leftOperand->listElements as $elements) {
            foreach ($elements as $index => $element) {
                if (!$element instanceof ArrayElement) {
                    continue;
                }

                $elementValue = $element->elementValue;

                if (!$elementValue instanceof ParserVariable) {
                    continue;
                }

                if (null === $elementValue || null === $elementValue->name) {
                    continue;
                }

                $varName = $elementValue->name->getText($leftOperand->getFileContents());
                $variableContext = $this->symbolFactory->context(
                    $varName,
                    $element->getStart(),
                    $element->getEndPosition(),
                    [
                        'symbol_type' => Symbol::VARIABLE,
                    ]
                );

                if (is_array($value) && isset($value[$index])) {
                    $variableContext = $variableContext->withValue($value[$index]);
                    $variableContext = $variableContext->withType(Type::fromString(gettype($value[$index])));
                }

                $frame->locals()->add(Variable::fromSymbolContext($variableContext));
            }
        }
    }

    private function walkSubscriptExpression(Frame $frame, SubscriptExpression $leftOperand, SymbolContext $rightContext)
    {
        if ($leftOperand->postfixExpression instanceof MemberAccessExpression) {
            $rightContext = $rightContext->withType(Type::array());
            $this->walkMemberAccessExpression($frame, $leftOperand->postfixExpression, $rightContext);
        }
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

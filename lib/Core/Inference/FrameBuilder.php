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

final class FrameBuilder
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SymbolInformationResolver
     */
    private $symbolInformationResolver;

    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    /**
     * @var array
     */
    private $injectedTypes = [];

    public function __construct(SymbolInformationResolver $symbolInformationResolver, Logger $logger)
    {
        $this->logger = $logger;
        $this->symbolInformationResolver = $symbolInformationResolver;
        $this->symbolFactory = new SymbolFactory();
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
            // New scope, new frame.
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

        $typeInformation = $this->resolveNode($frame, $node->qualifiedName);
        $information = $this->symbolFactory->information(
            $node->variableName->getText($node->getFileContents()),
            $node->variableName->getStartPosition(),
            $node->variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $typeInformation->type(),
            ]
        );

        $frame->locals()->add(Variable::fromSymbolInformation($information));
    }

    private function walkAssignment(Frame $frame, AssignmentExpression $node)
    {
        if ($node->leftOperand instanceof ParserVariable) {
            return $this->walkParserVariable($frame, $node);
        }

        if ($node->leftOperand instanceof MemberAccessExpression) {
            return $this->walkMemberAccessExpression($frame, $node);
        }

        $this->logger->warning(sprintf(
            'Do not know how to assign to left operand "%s"',
            get_class($node->leftOperand)
        ));
    }

    private function walkParserVariable(Frame $frame, AssignmentExpression $node)
    {
        $name = $node->leftOperand->name->getText($node->getFileContents());
        $symbolInformation = $this->resolveNode($frame, $node->rightOperand);
        $information = $this->symbolFactory->information(
            $name,
            $node->leftOperand->getStart(),
            $node->leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $symbolInformation->type(),
                'value' => $symbolInformation->value(),
            ]
        );

        $frame->locals()->add(Variable::fromSymbolInformation($information));
    }

    private function walkMemberAccessExpression(Frame $frame, AssignmentExpression $node)
    {
        $variable = $node->leftOperand->dereferencableExpression;

        // we do not track assignments to other classes.
        if (false === in_array($variable, [ '$this', 'self' ])) {
            return;
        }

        $memberNameNode = $node->leftOperand->memberName;
        $typeInformation = $this->resolveNode($frame, $node->rightOperand);

        // TODO: Sort out this mess.
        //       If the node is not a token (e.g. it is a variable) then
        //       evaluate the variable (e.g. $this->$foobar);
        if ($memberNameNode instanceof Token) {
            $memberName = $memberNameNode->getText($node->getFileContents());
        } else {
            $memberNameInfo = $this->resolveNode($frame, $memberNameNode);

            if (false === is_string($memberNameInfo->value())) {
                return;
            }

            $memberName = $memberNameInfo->value();
        }

        $information = $this->symbolFactory->information(
            $memberName,
            $node->leftOperand->getStart(),
            $node->leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $typeInformation->type(),
                'value' => $typeInformation->value(),
            ]
        );

        $frame->properties()->add(Variable::fromSymbolInformation($information));
    }

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
            $information = $this->symbolFactory->information(
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
            $frame->locals()->add(Variable::fromSymbolInformation($information));
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

            $symbolInformation = $this->resolveNode($frame, $parameterNode);

            $information = $this->symbolFactory->information(
                $parameterName,
                $parameterNode->getStart(),
                $parameterNode->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                    'type' => $symbolInformation->type(),
                    'value' => $symbolInformation->value(),
                ]
            );

            $frame->locals()->add(Variable::fromSymbolInformation($information));
        }
    }

    private function injectVariablesFromComment(Frame $frame, Node $node)
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();

        if (!preg_match('{var (\$?\w+) (\$?\w+)}', $comment, $matches)) {
            return;
        }

        $type = $matches[1];
        $varName = $matches[2];

        // detect non-standard
        if (substr($type, 0, 1) == '$') {
            list($varName, $type) = [$type, $varName];
        }

        $varName = ltrim($varName, '$');

        $this->injectedTypes[$varName] = $this->symbolInformationResolver->resolveQualifiedNameType($node, $type);
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

            $variableInformation = $this->symbolFactory->information(
                $varName,
                $element->getStart(),
                $element->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                ]
            );
            $varName = $variableInformation->symbol()->name();

            // if not in parent scope, then we know nothing about it
            // add it with above information and continue
            // TODO: Do we infer the type hint??
            if (0 === $parentVars->byName($varName)->count()) {
                $frame->locals()->add(Variable::fromSymbolInformation($variableInformation));
                continue;
            }

            $variable = $parentVars->byName($varName)->last();

            $variableInformation = $variableInformation
                ->withType($variable->symbolInformation()->type())
                ->withValue($variable->symbolInformation()->value());

            $frame->locals()->add(Variable::fromSymbolInformation($variableInformation));
        }
    }

    private function walkVariable(Frame $frame, ParserVariable $node)
    {
        if (false === $node->name instanceof Token) {
            return;
        }

        $information = $this->symbolFactory->information(
            $node->name->getText($node->getFileContents()),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        $symbolName = $information->symbol()->name();
        if (false === isset($this->injectedTypes[$symbolName])) {
            return;
        }

        $information  =$information->withType($this->injectedTypes[$symbolName]);
        $frame->locals()->add(Variable::fromSymbolInformation($information));
        unset($this->injectedTypes[$symbolName]);
    }

    private function resolveNode(Frame $frame, $node)
    {
        $info = $this->symbolInformationResolver->resolveNode($frame, $node);

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
            return $node->getName();
        }

        if ($node instanceof AnonymousFunctionCreationExpression) {
            return '<anonymous>';
        }

        return '<unknown>';
    }
}

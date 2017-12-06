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
use Phpactor\WorseReflection\Core\Errors;

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

    private $errors = [];

    public function __construct(SymbolInformationResolver $symbolInformationResolver, Logger $logger)
    {
        $this->logger = $logger;
        $this->symbolInformationResolver = $symbolInformationResolver;
        $this->symbolFactory = new SymbolFactory();
    }

    public function errors(): Errors
    {
        return Errors::fromErrors($this->errors);
    }

    public function buildForNode(Node $node): Frame
    {
        $scopeNode = $node->getFirstAncestor(FunctionLike::class, SourceFileNode::class);

        if (null === $scopeNode) {
            $scopeNode = $node;
        }

        return $this->buildFromNode($scopeNode);
    }

    public function buildFromNode(Node $node): Frame
    {
        $frame = new Frame();

        if ($node instanceof FunctionLike) {
            $this->processFunctionLike($frame, $node);
        }

        $this->walkNode($frame, $node, $node->getEndPosition());

        return $frame;
    }

    private function walkNode(Frame $frame, Node $node, int $endPosition)
    {
        if ($node->getStart() > $endPosition) {
            return;
        }

        $this->processLeadingComment($frame, $node);

        if ($node instanceof ParserVariable) {
            $this->processVariable($frame, $node);
        }

        if ($node instanceof AssignmentExpression) {
            $this->processAssignment($frame, $node);
        }

        if ($node instanceof CatchClause) {
            $this->processExceptionCatch($frame, $node);
        }

        foreach ($node->getChildNodes() as $node) {
            $this->walkNode($frame, $node, $endPosition);
        }
    }

    private function processExceptionCatch(Frame $frame, CatchClause $node)
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

    private function processAssignment(Frame $frame, AssignmentExpression $node)
    {
        if ($node->leftOperand instanceof ParserVariable) {
            return $this->processParserVariable($frame, $node);
        }

        if ($node->leftOperand instanceof MemberAccessExpression) {
            return $this->processMemberAccessExpression($frame, $node);
        }

        $this->logger->warning(sprintf(
            'Do not know how to assign to left operand "%s"',
            get_class($node->leftOperand)
        ));
    }

    private function processParserVariable(Frame $frame, AssignmentExpression $node)
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

    private function processMemberAccessExpression(Frame $frame, AssignmentExpression $node)
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

    private function processFunctionLike(Frame $frame, FunctionLike $node)
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

    private function processLeadingComment(Frame $frame, Node $node)
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

        $parentNode = $node->getParent();

        if (null === $parentNode) {
            return;
        }

        $parentFrame = $this->buildForNode($parentNode);
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

    private function processVariable(Frame $frame, ParserVariable $node)
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

        if ($info->errors()) {
            $this->errors[] = $info;
        }

        return $info;
    }
}

<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Phpactor\WorseReflection\Core\Offset;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Phpactor\WorseReflection\Core\Logger;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\Parameter;

final class FrameBuilder
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var NodeValueResolver
     */
    private $symbolInformationResolver;

    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    public function __construct(SymbolInformationResolver $symbolInformationResolver, Logger $logger)
    {
        $this->logger = $logger;
        $this->symbolInformationResolver = $symbolInformationResolver;
        $this->symbolFactory = new SymbolFactory();
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

        $symbolInformation = $this->symbolInformationResolver->resolveNode($frame, $node->qualifiedName);
        $name = $node->variableName->getText($node->getFileContents());

        $frame->locals()->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt($node->variableName->start),
            $name,
            $this->symbolFactory->information(
                $node, [
                    'symbol_type' => Symbol::VARIABLE,
                    'type' => $symbolInformation->type(),
                ]
            )
        ));
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
        $symbolInformation = $this->symbolInformationResolver->resolveNode($frame, $node->rightOperand);

        $frame->locals()->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt($node->leftOperand->getStart()),
            $name,
            $this->symbolFactory->information(
                $node, [
                    'token' => $node->leftOperand->name,
                    'symbol_type' => Symbol::VARIABLE,
                    'type' => $symbolInformation->type(),
                    'value' => $symbolInformation->value(),
                ]
            )
        ));
    }

    private function processMemberAccessExpression(Frame $frame, AssignmentExpression $node)
    {
        $variable = $node->leftOperand->dereferencableExpression;

        // we do not track assignments to other classes.
        if (false === in_array($variable, [ '$this', 'self' ])) {
            return;
        }

        $memberNameNode = $node->leftOperand->memberName;
        $symbolInformation = $this->symbolInformationResolver->resolveNode($frame, $node->rightOperand);

        // TODO: Sort out this mess.
        if ($memberNameNode instanceof Token) {
            $memberName = $memberNameNode->getText($node->getFileContents());
        } else {
            $memberNameInfo = $this->symbolInformationResolver->resolveNode($frame, $memberNameNode);
            if (false === is_string($memberNameInfo->value())) {
                return;
            }
            $memberName = $memberNameInfo->value();
        }

        $frame->properties()->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt($node->leftOperand->getStart()),
            $memberName,
            $this->symbolFactory->information(
                $node, [
                    'member_type' => Symbol::VARIABLE,
                    'token' => $memberNameNode instanceof Token ? $memberNameNode : null,
                    'type' => $symbolInformation->type(),
                    'value' => $symbolInformation->value(),
                ]
            )
        ));
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
            $classType = $this->symbolInformationResolver->resolveNode($frame, $classNode)->type();
            // add this and self
            $frame->locals()->add(Variable::fromOffsetNameAndValue(
                Offset::fromInt($node->getStart()),
                '$this',
                $this->symbolFactory->information(
                    $node, [
                        'type' => $classType,
                        'symbol_type' => Symbol::VARIABLE,
                    ]
                )
            ));
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
            $symbolInformation = $this->symbolInformationResolver->resolveNode($frame, $parameterNode);
            $frame->locals()->add(
                Variable::fromOffsetNameAndValue(
                    Offset::fromInt($parameterNode->getStart()),
                    $parameterName,
                    $this->symbolFactory->information(
                        $parameterNode, [
                            'token' => $parameterNode->variableName,
                            'symbol_type' => Symbol::VARIABLE,
                            'type' => $symbolInformation->type(),
                            'value' => $symbolInformation->value(),
                        ]
                    )
                )
            );
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

        $frame->locals()->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt($node->getStart()),
            $varName,
            $this->symbolFactory->information(
                $node, [
                    'symbol_type' => Symbol::VARIABLE,
                    'type' => $this->symbolInformationResolver->resolveQualifiedName($node, $type)
                ]
            )
        ));
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

            $symbolInformation = $this->symbolFactory->information(
                $element, [
                    'symbol_type' => Symbol::VARIABLE,
                ]
            );

            if (0 === $parentVars->byName($varName)->count()) {
                $frame->locals()->add(
                    Variable::fromOffsetNameAndValue(
                        Offset::fromInt($element->getStart()),
                        $varName,
                        $symbolInformation
                    )
                );
                continue;
            }

            $variable = $parentVars->byName($varName)->last();
            $symbolInformation = $symbolInformation
                ->withType($variable->symbolInformation()->type())
                ->withValue($variable->symbolInformation()->value());

            $frame->locals()->add(
                Variable::fromOffsetNameAndValue(
                    Offset::fromInt($node->getStart()),
                    $varName,
                    $symbolInformation
                )
            );

        }
    }
}

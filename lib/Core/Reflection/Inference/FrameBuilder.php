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

    public function buildFromNode(Node $node)
    {
        $frame = new Frame();
        $this->walkNode($frame, $node, $node->getEndPosition());

        return $frame;
    }

    public function buildForNode(Node $node)
    {
        $scopeNode = $node->getFirstAncestor(MethodDeclaration::class, FunctionDeclaration::class, AnonymousFunctionCreationExpression::class, SourceFileNode::class);

        return $this->buildFromNode($scopeNode);
    }

    private function walkNode(Frame $frame, Node $node, int $endPosition)
    {
        if ($node->getStart() > $endPosition) {
            return;
        }

        $this->processLeadingComment($frame, $node);

        if ($node instanceof MethodDeclaration) {
            $this->processMethodDeclaration($frame, $node);
        }

        if ($node instanceof AssignmentExpression) {
            $this->processAssignment($frame, $node);
        }

        foreach ($node->getChildNodes() as $node) {
            $this->walkNode($frame, $node, $endPosition);
        }
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

        $memberName = $node->leftOperand->memberName->getText($node->getFileContents());
        $symbolInformation = $this->symbolInformationResolver->resolveNode($frame, $node->rightOperand);

        $frame->properties()->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt($node->leftOperand->getStart()),
            $memberName,
            $this->symbolFactory->information(
                $node, [
                    'member_type' => Symbol::VARIABLE,
                    'token' => $node->leftOperand->memberName instanceof Token ? $node->leftOperand->memberNName : null,
                    'type' => $symbolInformation->type(),
                    'value' => $symbolInformation->value(),
                ]
            )
        ));
    }

    private function processMethodDeclaration(Frame $frame, MethodDeclaration $node)
    {
        $namespace = $node->getNamespaceDefinition();
        $classNode = $node->getFirstAncestor(
            ClassDeclaration::class,
            InterfaceDeclaration::class,
            TraitDeclaration::class
        );
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

        if (null === $node->parameters) {
            return;
        }

        foreach ($node->parameters->getElements() as $parameterNode) {
            $parameterName = $parameterNode->variableName->getText($node->getFileContents());
            $symbolInformation = $this->symbolInformationResolver->resolveNode($frame, $parameterNode);
            $frame->locals()->add(
                Variable::fromOffsetNameAndValue(
                    Offset::fromInt($parameterNode->getStart()),
                    $parameterName,
                    $this->symbolFactory->information(
                        $parameterNode, [
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

        if (preg_match('{var \$(\w+) (\w+)}', $comment, $matches)) {
            $frame->locals()->add(Variable::fromOffsetNameAndValue(
                Offset::fromInt($node->getStart()),
                '$' . $matches[1],
                $this->symbolFactory->information(
                    $node, [
                        'symbol_type' => Symbol::VARIABLE,
                        'type' => $this->symbolInformationResolver->resolveQualifiedName($node, $matches[2])
                    ]
                )
            ));
        }
    }
}

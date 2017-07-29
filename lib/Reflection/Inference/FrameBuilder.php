<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Phpactor\WorseReflection\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Reflection\Inference\Value;
use Phpactor\WorseReflection\Offset;

final class FrameBuilder
{
    /**
     * @var NodeTypeResolver
     */
    private $typeResolver;

    public function __construct(NodeValueResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    public function buildFromNode(Node $node)
    {
        $frame = new Frame();
        $this->walkNode($frame, $node, $node->getEndPosition());
        return $frame;
    }

    private function walkNode(Frame $frame, Node $node, int $endPosition)
    {
        if ($node->getStart() > $endPosition) {
            return;
        }

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
        if (!$node->leftOperand instanceof ParserVariable) {
            return;
        }

        $name = $node->leftOperand->name->getText($node->getFileContents());
        $value = $this->typeResolver->resolveNode($frame, $node->rightOperand);

        $frame->locals()->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt($node->leftOperand->getStart()),
            $name,
            Value::fromTypeAndValue(
                $value->type(),
                $value->value()
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
        $classType = $this->typeResolver->resolveNode($frame, $classNode);

        $frame->locals()->add(Variable::fromOffsetNameAndValue(Offset::fromInt($node->getStart()), '$this', Value::fromType($classType->type())));
        $frame->locals()->add(Variable::fromOffsetNameAndValue(Offset::fromInt($node->getStart()), 'self', Value::fromType($classType->type())));

        if (null === $node->parameters) {
            return;
        }

        foreach ($node->parameters->getElements() as $parameterNode) {
            $parameterName = $parameterNode->variableName->getText($node->getFileContents());
            $value = $this->typeResolver->resolveNode($frame, $parameterNode);
            $frame->locals()->add(
                Variable::fromOffsetNameAndValue(
                    Offset::fromInt($parameterNode->getStart()),
                    $parameterName,
                    Value::fromTypeAndValue(
                        $value->type(),
                        $value->value()
                    )
                )
            );
        }
    }
}


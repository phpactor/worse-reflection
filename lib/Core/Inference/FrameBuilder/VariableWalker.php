<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Types;

class VariableWalker extends AbstractWalker
{
    /**
     * @var DocBlockFactory
     */
    private $docblockFactory;

    /**
     * @var array
     */
    private $injectedTypes = [];

    /**
     * @var FullyQualifiedNameResolver
     */
    private $nameResolver;

    public function __construct(
        DocBlockFactory $docblockFactory,
        FullyQualifiedNameResolver $nameResolver
    ) {
        $this->docblockFactory = $docblockFactory;
        $this->nameResolver = $nameResolver;
    }

    public function canWalk(Node $node): bool
    {
        return true;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        $docblockTypes = $this->injectVariablesFromComment($frame, $node);

        if (!$node instanceof Variable) {
            return $frame;
        }

        if (false === $node->name instanceof Token) {
            return $frame;
        }

        $context = $this->symbolFactory()->context(
            $node->name->getText($node->getFileContents()),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        $symbolName = $context->symbol()->name();

        if (!isset($this->injectedTypes[$symbolName]) && $docblockTypes->count() === 0) {
            return $frame;
        }

        if (isset($this->injectedTypes[$symbolName])) {
            $docblockTypes = Types::fromTypes([$this->injectedTypes[$symbolName]]);
            unset($this->injectedTypes[$symbolName]);
        }

        $context = $context->withTypes($docblockTypes);
        $frame->locals()->add(WorseVariable::fromSymbolContext($context));

        return $frame;
    }

    private function injectVariablesFromComment(Frame $frame, Node $node): Types
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();
        $docblock = $this->docblockFactory->create($comment);

        if (false === $docblock->isDefined()) {
            return Types::empty();
        }

        $vars = $docblock->vars();
        $resolvedTypes = [];

        /** @var DocBlockVar $var */
        foreach ($docblock->vars() as $var) {
            $resolvedType = $this->nameResolver->resolve(
                $node,
                $var->types()->best()
            );
            $this->injectedTypes[ltrim($var->name(), '$')] = $resolvedType;
            $resolvedTypes[] = $resolvedType;
        }

        return Types::fromTypes($resolvedTypes);
    }
}

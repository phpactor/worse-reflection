<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;

class VariableWalker
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
     * @var SymbolFactory
     */
    private $symbolFactory;

    /**
     * @var FullyQualifiedNameResolver
     */
    private $nameResolver;

    public function __construct(
        SymbolFactory $symbolFactory,
        DocBlockFactory $docblockFactory,
        FullyQualifiedNameResolver $nameResolver
    ) {
        $this->symbolFactory = $symbolFactory;
        $this->docblockFactory = $docblockFactory;
        $this->nameResolver = $nameResolver;
    }

    public function canWalk(Node $node): bool
    {
        return true;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        $this->injectVariablesFromComment($frame, $node);

        if (!$node instanceof Variable) {
            return $frame;
        }

        if (false === $node->name instanceof Token) {
            return $frame;
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
            return $frame;
        }

        $context = $context->withType($this->injectedTypes[$symbolName]);
        $frame->locals()->add(WorseVariable::fromSymbolContext($context));
        unset($this->injectedTypes[$symbolName]);

        return $frame;
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
}

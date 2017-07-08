<?php

namespace Phpactor\WorseReflection\Reflection\Formatted;

use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;

final class MethodHeader
{
    /**
     * @var MethodDeclaration
     */
    private $node;

    private function __construct(MethodDeclaration $node)
    {
        $this->node = $node;
    }

    public static function fromNode(MethodDeclaration $node)
    {
        return new self($node);
    }

    public function __toString()
    {
        $length = $this->node->getEndPosition(); 
        $statement = $this->node->compoundStatementOrSemicolon;

        if ($statement instanceof CompoundStatementNode) {
            $length = $statement->openBrace->start - $this->node->getStart();
        }

        if ($statement instanceof Token) {
            $length = $statement->start - $this->node->getStart();
        }

        return trim(substr(
            $this->node->getText(),
            0,
            $length
        ));
    }
}


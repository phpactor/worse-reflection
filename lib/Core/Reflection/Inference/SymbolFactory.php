<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;

class SymbolFactory
{
    public function symbol(Node $node, array $options): Symbol
    {
        $options = array_merge([
            'symbol_type' => null,
            'token' => null,
        ], $options);

        $name = $this->name($node, $options['token']);
        $position = $this->position($node, $options['token']);

        return Symbol::fromTypeNameAndPosition($options['symbol_type'], $name, $position);
    }

    public function information(Node $node, array $options): SymbolInformation
    {
        $options = array_merge([
            'symbol_type' => null,
            'token' => null,
            'type' => null,
        ], $options);

        $symbol = $this->symbol($node, [
            'symbol_type' => $options['symbol_type'],
            'token' => $options['token'],
        ]);

        $information = SymbolInformation::for($symbol);

        if ($options['type']) {
            $information = $information->withType($options['type']);
        }

        return $information;
    }

    private function name(Node $node, Token $token = null)
    {
        if ($token) {
            return $token->getText($node->getFileContents());
        }

        return $node->getText();
    }

    private function position(Node $node, Token $token = null)
    {
        if ($token) {
            return Position::fromStartAndEnd($token->start, $token->start + $token->length);
        }

        return Position::fromStartAndEnd($node->getStart(), $node->getEndPosition());
    }
}

<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Position;

class SymbolFactory
{
    public function symbol(Node $node, array $options): Symbol
    {
        $options = array_merge([
            'symbol_type' => Symbol::UNKNOWN,
            'token' => null,
        ], $options);

        if (null !== $options['token'] && false === $options['token'] instanceof Token) {
            throw new \InvalidArgumentException(sprintf(
                'Token must be of type %s, got %s',
                Token::class, is_object($options['token']) ? get_class($options['token']) : gettype($options['token'])
            ));
        }

        $name = $this->name($node, $options['token']);
        $position = $this->position($node, $options['token']);

        return Symbol::fromTypeNameAndPosition($options['symbol_type'], $name, $position);
    }

    public function information(Node $node, array $options): SymbolInformation
    {
        $options = array_merge([
            'container_type' => null,
            'symbol_type' => Symbol::UNKNOWN,
            'token' => null,
            'type' => null,
            'value' => null,
        ], $options);

        $symbol = $this->symbol($node, [
            'symbol_type' => $options['symbol_type'],
            'token' => $options['token'],
        ]);

        $information = SymbolInformation::for($symbol);

        if ($options['type']) {
            $information = $information->withType($options['type']);
        }

        if ($options['container_type']) {
            $information = $information->withContainerType($options['container_type']);
        }

        if (null !== $options['value']) {
            $information = $information->withValue($options['value']);
        }

        return $information;
    }

    private function name(Node $node, $token = null)
    {
        return ltrim($this->rawName($node, $token), '$');
    }

    private function rawName(Node $node, $token = null)
    {
        if ($token) {
            return $token->getText($node->getFileContents());
        }

        return $node->getText();
    }

    private function position(Node $node, $token = null)
    {
        if ($token && $token instanceof Token) {
            return Position::fromStartAndEnd($token->start, $token->start + $token->length);
        }

        return Position::fromStartAndEnd($node->getStart(), $node->getEndPosition());
    }
}

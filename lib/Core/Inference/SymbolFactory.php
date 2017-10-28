<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Position;
use Webmozart\Assert\Assert;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Type;

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
                Token::class,
                is_object($options['token']) ? get_class($options['token']) : gettype($options['token'])
            ));
        }

        $name = $this->name($node, $options['token']);
        $position = $this->position($node, $options['token']);

        return Symbol::fromTypeNameAndPosition($options['symbol_type'], $name, $position);
    }

    public function information(string $symbolName, int $start, int $end, array $config = []): SymbolInformation
    {
        $defaultConfig = [
            'symbol_type' => Symbol::UNKNOWN,
            'container_type' => null,
            'type' => null,
            'value' => null,
        ];

        if ($diff = array_diff(array_keys($config), array_keys($defaultConfig))) {
            throw new \RuntimeException(sprintf(
                'Invalid keys "%s", valid keys "%s"',
                implode('", "', $diff), implode('", "', array_keys($defaultConfig))
            ));
        }

        $config = array_merge($defaultConfig, $config);
        $position = Position::fromStartAndEnd($start, $end);
        $symbol = Symbol::fromTypeNameAndPosition(
            $config['symbol_type'],
            $symbolName,
            $position
        );

        return $this->informationFromParameters(
            $symbol,
            $config['type'],
            $config['container_type'],
            $config['value']
        );
    }

    private function informationFromParameters(
        Symbol $symbol,
        Type $type = null,
        Type $containerType = null,
        $value = null
    ): SymbolInformation
    {
        $information = SymbolInformation::for($symbol);

        if ($type) {
            $information = $information->withType($type);
        }

        if ($containerType) {
            $information = $information->withContainerType($containerType);
        }

        if (null !== $value) {
            $information = $information->withValue($value);
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

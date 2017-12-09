<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;

class SymbolFactory
{
    public function context(string $symbolName, int $start, int $end, array $config = []): SymbolContext
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
                implode('", "', $diff),
                implode('", "', array_keys($defaultConfig))
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
    ): SymbolContext {
        $information = SymbolContext::for($symbol);

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
}

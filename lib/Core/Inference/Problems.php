<?php

namespace Phpactor\WorseReflection\Core\Inference;

final class Problems implements \IteratorAggregate
{
    private $problems = [];

    public static function create(): Problems
    {
        return new self();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->problems);
    }

    public function add(SymbolInformation $problem)
    {
        $this->problems[] = $problem;
    }

    public function __toString()
    {
        $lines = [];
        /** @var SymbolInformation $symbolInformation */
        foreach ($this->problems as $symbolInformation) {
            $lines[] = sprintf(
                '%s:%s %s',
                $symbolInformation->symbol()->position()->start(),
                $symbolInformation->symbol()->position()->end(),
                implode(', ', $symbolInformation->errors())
            );
        }

        return implode(PHP_EOL, $lines);
    }
}

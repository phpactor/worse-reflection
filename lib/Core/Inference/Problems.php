<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Inference\Problems;

final class Problems implements \IteratorAggregate
{
    private $problems = [];
    public $traces = [];

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
        $trace = debug_backtrace();
        $this->traces[] = array_splice($trace, 0, 2);
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

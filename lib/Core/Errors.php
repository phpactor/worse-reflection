<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Inference\SymbolInformation;

final class Errors implements \IteratorAggregate
{
    private $errors = [];

    private function __construct($errors)
    {
        foreach ($errors as $item) {
            $this->add($item);
        }
    }

    public static function fromErrors(array $errors): Errors
    {
         return new self($errors);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->errors);
    }

    private function add(SymbolInformation $item)
    {
        $this->errors[] = $item;
    }

    public function __toString()
    {
        $lines = [];
        /** @var SymbolInformation $symbolInformation */
        foreach ($this->errors as $symbolInformation) {
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

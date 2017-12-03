<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;

final class NameImports implements \IteratorAggregate
{
    private $nameImports = [];

    private function __construct($nameImports)
    {
        foreach ($nameImports as $short => $item) {
            $this->add($short, $item);
        }
    }

    public static function fromNames(array $nameImports): NameImports
    {
         return new self($nameImports);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->nameImports);
    }

    private function add(string $short, Name $item)
    {
        $this->nameImports[$short] = $item;
    }
}

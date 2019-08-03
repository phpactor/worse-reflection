<?php

namespace Phpactor\WorseReflection\Core;

use RuntimeException;

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

    public function getByAlias(string $alias)
    {
        if (!isset($this->nameImports[$alias])) {
            throw new RuntimeException(sprintf(
                'Unknown alias "%s", known aliases: "%s"',
                $alias,
                implode('", "', array_keys($this->nameImports))
            ));
        }

        return $this->nameImports[$alias];
    }

    public function hasAlias(string $alias)
    {
        return isset($this->nameImports[$alias]);
    }
}

<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Type;

final class Types implements \IteratorAggregate
{
    private $types = [];

    private function __construct($inferredTypes)
    {
        foreach ($inferredTypes as $item) {
            $this->add($item);
        }
    }

    public static function empty()
    {
        return new self([]);
    }

    public function guess(): Type
    {
        foreach ($this->types as $type) {
            return $type;
        }

        return Type::unknown();
    }

    public static function fromTypes(array $inferredTypes): Types
    {
        return new self($inferredTypes);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->types);
    }

    public function merge(Types $types) : Types
    {
        return new self(array_merge(
            $this->types,
            $types->types
        ));
    }

    private function add(Type $item)
    {
        $this->types[] = $item;
    }
}

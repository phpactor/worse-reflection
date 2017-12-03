<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Type;

final class Types implements \IteratorAggregate
{
    private $inferredTypes = [];

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

    public static function fromInferredTypes(array $inferredTypes): Types
    {
        return new self($inferredTypes);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->inferredTypes);
    }

    public function merge(Types $types) : Types
    {
        return new self(array_merge(
            $this->inferredTypes,
            $types->inferredTypes
        ));
    }

    private function add(Type $item)
    {
        $this->inferredTypes[] = $item;
    }
}

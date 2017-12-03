<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Type;
use RuntimeException;

final class InferredTypes implements \IteratorAggregate
{
    private $inferredTypes = [];

    private function __construct($inferredTypes)
    {
        foreach ($inferredTypes as $item) {
            $this->add($item);
        }
    }

    public static function fromInferredTypes(array $inferredTypes): InferredTypes
    {
         return new self($inferredTypes);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->inferredTypes);
    }

    public function merge(InferredTypes $types) : InferredTypes
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

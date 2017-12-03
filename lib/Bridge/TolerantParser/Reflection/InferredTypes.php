<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Type;

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

    private function add(Type $item)
    {
        $this->inferredTypes[] = $item;
    }
}

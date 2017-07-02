<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use Microsoft\PhpParser\Node;

abstract class AbstractReflectionCollection implements \IteratorAggregate
{
    protected $items = [];
    protected $reflector;

    protected function __construct(Reflector $reflector, array $items)
    {
        $this->reflector = $reflector;
        $this->items = $items;
    }

    public function keys(): array
    {
        return array_keys($this->items);
    }

    public function merge(AbstractReflectionCollection $collection)
    {
        if (false === $collection instanceof static) {
            throw new \InvalidArgumentException(sprintf(
                'Collection must be instance of "%s"',
                static::class
            ));
        }

        $items = $this->items;

        foreach ($collection as $key => $value) {
            $items[$key] = $value;
        }

        return new static($this->reflector, $items);
    }

    protected function assertCollectionType(AbstractReflectionCollection $collection): bool
    {
    }

    public function get(string $name)
    {
        if (!isset($this->items[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown item "%s", known items: "%s"',
                $name, implode('", "', array_keys($this->items))
            ));
        }

        return $this->items[$name];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}

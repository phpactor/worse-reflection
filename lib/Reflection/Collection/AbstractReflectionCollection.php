<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;

abstract class AbstractReflectionCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $items = [];
    protected $reflector;

    protected function __construct(Reflector $reflector, array $items)
    {
        $this->reflector = $reflector;
        $this->items = $items;
    }

    public function count()
    {
        return count($this->items);
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

    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetGet($name)
    {
        return $this->get($name);
    }

    public function offsetSet($name, $value)
    {
        throw new \BadMethodCallException('Collections are immutable');
    }

    public function offsetUnset($name)
    {
        throw new \BadMethodCallException('Collections are immutable');
    }

    public function offsetExists($name)
    {
        return isset($this->items[$name]);
    }

}

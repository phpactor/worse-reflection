<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use IteratorAggregate;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;

interface ReflectionCollection extends IteratorAggregate
{
    public function count();

    public function keys(): array;

    public function merge(ReflectionCollection $collection);

    public function get(string $name);

    /**
     * Return first item from the collection of throw an ItemNotFound exception.
     *
     * @throws ItemNotFound
     */
    public function first();

    /**
     * Return last item from the collection of throw an ItemNotFound exception.
     *
     * @throws ItemNotFound
     */
    public function last();

    public function has(string $name): bool;

    public function getIterator();

    public function offsetGet($name);

    public function offsetSet($name, $value);

    public function offsetUnset($name);

    public function offsetExists($name);
}

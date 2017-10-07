<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\AbstractReflectionCollection;

interface ReflectionCollection
{
    public function count();

    public function keys(): array;

    public static function empty(ServiceLocator $serviceLocator);

    public function merge(AbstractReflectionCollection $collection);

    public function get(string $name);

    public function first();

    public function last();

    public function has(string $name): bool;

    public function getIterator();

    public function offsetGet($name);

    public function offsetSet($name, $value);

    public function offsetUnset($name);

    public function offsetExists($name);
}

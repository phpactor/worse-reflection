<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use AppendIterator;
use IteratorIterator;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use RuntimeException;

class ChainReflectionMemberCollection implements ReflectionMemberCollection
{
    /**
     * @var ReflectionMemberCollection[]
     */
    private $collections = [];

    /**
     * @param ReflectionMemberCollection[] $collections
     */
    public function __construct(array $collections)
    {
        foreach ($collections as $collection) {
            $this->add($collection);
        }
    }

    public static function fromCollections(array $collections): self
    {
        return new self($collections);
    }

    /**
     * @return IteratorIterator
     */
    public function getIterator()
    {
        $iterator = new AppendIterator();
        foreach ($this->collections as $collection) {
            $iterator->append($collection->getIterator());
        }

        return $iterator;
    }

    public function count()
    {
        return array_reduce($this->collections, function ($acc, ReflectionMemberCollection $collection) {
            $acc += count($collection);
            return $acc;
        }, 0);
    }

    public function keys(): array
    {
        return array_reduce($this->collections, function ($acc, ReflectionMemberCollection $collection) {
            $acc = array_merge($acc, $collection->keys());
            return $acc;
        }, []);
    }

    public function merge(ReflectionCollection $collection)
    {
        $new = new self($this->collections);
        $new->add($collection);
        return $new;
    }

    public function get(string $name)
    {
        $known = [];
        foreach ($this->collections as $collection) {
            $known[] = $name;
            if ($collection->has($name)) {
                return $collection->get($name);
            }
        }

        throw new ItemNotFound(sprintf(
            'Unknown item "%s", known items: "%s"',
            $name,
            implode('", "', $known)
        ));
    }

    public function first()
    {
        foreach ($this->collections as $collection) {
            return $collection->first();
        }

        throw new ItemNotFound(
            'None of the collections have items'
        );
    }

    public function last()
    {
        $last = null;

        foreach ($this->collections as $collection) {
            $last = $collection->last();
        }

        if ($last) {
            return $last;
        }

        throw new ItemNotFound(
            'None of the collections have items'
        );
    }

    public function has(string $name): bool
    {
        foreach ($this->collections as $collection) {
            if ($collection->has($name)) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet($name)
    {
        return $this->get($name);
    }

    public function offsetSet($name, $value)
    {
        throw new RuntimeException(sprintf(
            'ChainColleciton is immutable'
        ));
    }

    public function offsetUnset($name)
    {
        throw new RuntimeException(sprintf(
            'ChainColleciton is immutable'
        ));
    }

    public function offsetExists($name)
    {
        return $this->has($name);
    }

    public function byVisibilities(array $visibilities): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->byVisibilities($visibilities);
        }

        return new self($collections);
    }

    public function belongingTo(ClassName $class): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->belongingTo($class);
        }

        return new self($collections);
    }

    public function atOffset(int $offset): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->atOffset($offset);
        }

        return new self($collections);
    }

    private function add(ReflectionMemberCollection $collection)
    {
        $this->collections[] = $collection;
    }

    public function byName(string $name): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->byName($name);
        }

        return new self($collections);
    }
}

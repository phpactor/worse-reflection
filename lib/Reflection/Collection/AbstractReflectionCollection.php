<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use Microsoft\PhpParser\Node;

abstract class AbstractReflectionCollection implements \IteratorAggregate
{
    protected $items = [];
    private $reflector;

    public function __construct(Reflector $reflector, array $items)
    {
        $this->reflector = $reflector;
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    abstract protected function add(Node $method);
    abstract protected function createFromNode(Reflector $reflector, Node $node);

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function get(string $name)
    {
        if (!isset($this->items[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown item "%s", known items: "%s"',
                $name, implode('", "', array_keys($this->items))
            ));
        }

        return $this->createFromNode($this->reflector, $this->items[$name]);
    }
}

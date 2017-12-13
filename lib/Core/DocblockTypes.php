<?php

namespace Phpactor\WorseReflection\Core;

final class DocblockTypes implements \IteratorAggregate
{
    private $docblockTypes = [];

    private function __construct($docblockTypes)
    {
        foreach ($docblockTypes as $name => $item) {
            $this->add($item);
        }
    }

    public static function fromDocblockTypes(array $docblockTypes): DocblockTypes
    {
         return new self($docblockTypes);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->docblockTypes);
    }

    private function add(DocblockType $item)
    {
        $this->docblockTypes[] = $item;
    }
}

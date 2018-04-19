<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection as CoreReflectionMemberCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

class ReflectionMemberCollection extends AbstractReflectionCollection implements CoreReflectionMemberCollection
{
    public function byVisibilities(array $visibilities): CoreReflectionMemberCollection
    {
        $items = [];
        foreach ($this as $key => $item) {
            foreach ($visibilities as $visibility) {
                if ($item->visibility() != $visibility) {
                    continue;
                }

                $items[$key] = $item;
            }
        }

        return new static($this->serviceLocator, $items);
    }

    public function belongingTo(ClassName $class): CoreReflectionMemberCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMember $item) use ($class) {
            return $item->declaringClass()->name() == $class;
        }));
    }

    public function atOffset(int $offset): CoreReflectionMemberCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMember $item) use ($offset) {
            return $item->position()->start() <= $offset && $item->position()->end() >= $offset;
        }));
    }
}

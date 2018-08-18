<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;

class VirtualReflectionMethodCollectionTest extends VirtualReflectionMemberTestCase
{
    public function collection(array $names): ReflectionCollection
    {
        $items = [];
        foreach ($names as $name) {
            $item = $this->prophesize(ReflectionMethod::class);
            $item->name()->willReturn($name);
            $items[] = $item->reveal();
        }
        return VirtualReflectionMethodCollection::fromReflectionMethods($items);
    }
}

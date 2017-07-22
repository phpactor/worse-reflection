<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Collection;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Reflector;

class ReflectionClassCollectionTest extends TestCase
{
    private $reflector;
    private $reflection1;
    private $reflection2;
    private $reflection3;

    protected function setUp()
    {
        $this->reflector = $this->prophesize(Reflector::class);
        $this->reflection1 = $this->prophesize(ReflectionClass::class);
        $this->reflection2 = $this->prophesize(ReflectionClass::class);
        $this->reflection3 = $this->prophesize(ReflectionClass::class);
    }

    /**
     * @testdox It returns only concrete classes.
     */
    public function testConcrete()
    {
        $this->reflection1->isConcrete()->willReturn(false);
        $this->reflection2->isConcrete()->willReturn(true);
        $this->reflection3->isConcrete()->willReturn(false);

        $collection = ReflectionClassCollection::fromReflections($this->reflector->reveal(), [
            $this->reflection1->reveal(), $this->reflection2->reveal(), $this->reflection3->reveal()
        ]);

        $this->assertCount(1, $collection->concrete());
    }
}

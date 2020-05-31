<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual\Collection;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionCollection;
use RuntimeException;

abstract class AbstractReflectionCollectionTestCase extends TestCase
{
    abstract public function collection(array $names): ReflectionCollection;

    public function testCount()
    {
        $this->assertEquals(2, $this->collection(['one', 'two'])->count());
    }

    public function testKeys()
    {
        $this->assertEquals(2, $this->collection(['one', 'two'])->count());
    }

    public function testMerge()
    {
        $collection = $this->collection(['one', 'two'])->merge($this->collection(['three', 'four']));
        $this->assertCount(4, $collection);
        $this->assertEquals(['one', 'two', 'three', 'four'], $collection->keys());
    }

    public function testMergeThrowsExceptionOnIncorrectType()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Collection must be instance of');
        $collection = $this->prophesize(ReflectionCollection::class)->reveal();
        $this->collection([])->merge($collection);
    }

    public function testGet()
    {
        $this->assertNotNull($this->collection(['one'])->get('one'));
    }

    public function testGetThrowsExceptionIfItemNotExisting()
    {
        $this->expectException(ItemNotFound::class);
        $this->collection(['one'])->get('two');
    }

    public function testFirst()
    {
        $this->assertNotNull($this->collection(['one', 'two', 'three'])->first());
    }

    public function testGetFirstThrowsExceptionIfColletionIsEmpty()
    {
        $this->expectException(ItemNotFound::class);
        $this->collection([])->first();
    }

    public function testLast()
    {
        $this->assertNotNull($this->collection(['one', 'two', 'three'])->last());
    }

    public function testHas()
    {
        $this->assertTrue($this->collection(['one'])->has('one'));
    }
}

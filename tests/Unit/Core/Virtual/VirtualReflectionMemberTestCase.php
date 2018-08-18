<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Visibility;

abstract class VirtualReflectionMemberTestCase extends TestCase
{
    protected $position;
    protected $declaringClass;
    protected $class;
    protected $name;
    protected $frame;
    protected $docblock;
    protected $scope;
    protected $visibility;
    protected $types;
    protected $type;

    public function setUp()
    {
        $this->position = Position::fromStartAndEnd(0, 0);
        $this->declaringClass = $this->prophesize(ReflectionClass::class);
        $this->class = $this->prophesize(ReflectionClass::class);
        $this->name = 'test_name';
        $this->frame = $this->prophesize(Frame::class);
        $this->docblock = $this->prophesize(DocBlock::class);
        $this->scope = $this->prophesize(ReflectionScope::class);
        $this->visibility = Visibility::public();
        $this->types = Types::empty();
        $this->type = Type::unknown();
    }

    abstract public function member(): ReflectionMember;

    public function testPosition()
    {
        $this->assertSame($this->position, $this->member()->position());
    }

    public function testDeclaringClass()
    {
        $this->assertSame($this->declaringClass->reveal(), $this->member()->declaringClass());
    }

    public function testClass()
    {
        $this->assertSame($this->class->reveal(), $this->member()->class());
    }

    public function testName()
    {
        $this->assertEquals($this->name, $this->member()->name());
    }

    public function testFrame()
    {
        $this->assertEquals($this->frame->reveal(), $this->member()->frame());
    }

    public function testDocblock()
    {
        $this->assertEquals($this->docblock->reveal(), $this->member()->docblock());
    }

    public function testScope()
    {
        $this->assertEquals($this->scope->reveal(), $this->member()->scope());
    }

    public function testVisibility()
    {
        $this->assertEquals($this->visibility, $this->member()->visibility());
    }

    public function testTypes()
    {
        $this->assertEquals($this->types, $this->member()->inferredTypes());
    }

    public function testType()
    {
        $this->assertEquals($this->type, $this->member()->type());
    }
}

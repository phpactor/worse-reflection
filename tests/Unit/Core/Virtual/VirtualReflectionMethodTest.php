<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Virtual;

use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;

class VirtualReflectionMethodTest extends VirtualReflectionMemberTestCase
{
    private $parameters;
    private $body;
    private $isAbstract;
    private $isStatic;

    public function setUp()
    {
        parent::setUp();
        $this->parameters = $this->prophesize(ReflectionParameterCollection::class);
        $this->body = NodeText::fromString('hello');
        $this->isAbstract = true;
        $this->isStatic = true;
    }

    /**
     * @return ReflectionMethod
     */
    public function member(): ReflectionMember
    {
        return new VirtualReflectionMethod(
            $this->position,
            $this->declaringClass->reveal(),
            $this->class->reveal(),
            $this->name,
            $this->frame->reveal(),
            $this->docblock->reveal(),
            $this->scope->reveal(),
            $this->visibility,
            $this->types,
            $this->type,
            $this->parameters->reveal(),
            $this->body,
            $this->isAbstract,
            $this->isStatic
        );
    }

    public function testParameters()
    {
        $this->assertEquals($this->parameters->reveal(), $this->member()->parameters());
    }

    public function testBody()
    {
        $this->assertEquals($this->body, $this->member()->body());
    }

    public function testIsAbstract()
    {
        $this->assertEquals($this->isAbstract, $this->member()->isAbstract());
    }

    public function testIsStatic()
    {
        $this->assertEquals($this->isStatic, $this->member()->isStatic());
    }

    public function testVirtual()
    {
        $this->assertTrue($this->member()->isStatic());
    }

    public function testReturnType()
    {
        $this->assertEquals(Type::unknown(), $this->member()->returnType());
    }
}

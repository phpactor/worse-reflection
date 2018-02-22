<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Reflector\ClassReflector;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector\MemonizedClassReflector;

class MemonizedClassReflectorTest extends TestCase
{
    /**
     * @var Classreflector|ObjectProphecy
     */
    private $innerReflector;

    /**
     * @var MemonizedClassReflector
     */
    private $reflector;

    public function setUp()
    {
        $this->innerReflector = $this->prophesize(ClassReflector::class);

        $this->reflector = new MemonizedClassReflector(
            $this->innerReflector->reveal()
        );
        $this->className = ClassName::fromString('Hello');
    }

    public function testReflectClass()
    {
        $this->innerReflector->reflectClass($this->className)->shouldBeCalledTimes(1);
        $this->reflector->reflectClass($this->className);
        $this->reflector->reflectClass($this->className);
        $this->reflector->reflectClass($this->className);
    }

    public function testReflectInterface()
    {
        $this->innerReflector->reflectInterface($this->className)->shouldBeCalledTimes(1);
        $this->reflector->reflectInterface($this->className);
        $this->reflector->reflectInterface($this->className);
        $this->reflector->reflectInterface($this->className);
    }

    public function testReflectTrait()
    {
        $this->innerReflector->reflectTrait($this->className)->shouldBeCalledTimes(1);
        $this->reflector->reflectTrait($this->className);
        $this->reflector->reflectTrait($this->className);
        $this->reflector->reflectTrait($this->className);
    }

    public function testReflectClassLike()
    {
        $this->innerReflector->reflectClassLike($this->className)->shouldBeCalledTimes(1);
        $this->reflector->reflectClassLike($this->className);
        $this->reflector->reflectClassLike($this->className);
        $this->reflector->reflectClassLike($this->className);
    }
}

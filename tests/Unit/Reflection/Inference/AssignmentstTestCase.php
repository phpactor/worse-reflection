<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\Reflection\Inference\Assignments;

abstract class AssignmentstTestCase extends TestCase
{
    abstract protected function assignments(): Assignments;

    public function testGetSetHas()
    {
        $assignments = $this->assignments();
        $this->assertFalse($assignments->has('hello'));

        $type = Type::fromString('Foobar');
        $assignments->set('hello', $type);
        $this->assertTrue($assignments->has('hello'));
        $this->assertSame($type, $assignments->get('hello'));
    }
}

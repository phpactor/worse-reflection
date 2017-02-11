<?php

namespace DTL\WorseReflection\Tests\Unit\Frame;

use DTL\WorseReflection\Frame\FrameStack;
use DTL\WorseReflection\Frame\Frame;
use PhpParser\Node\Expr\Variable;

class FrameStackTest extends \PHPUnit_Framework_TestCase
{
    private $stack;

    public function __construct()
    {
        $this->stack = new FrameStack();
    }

    /**
     * It should spawn a new frame.
     * It should pop frames from the stack.
     */
    public function testSpawn()
    {
        $frame = $this->stack->spawn();
        $this->assertInstanceOf(Frame::class, $frame);
        $this->assertSame($frame, $this->stack->pop());
    }

    /**
     * It should throw an exception if there are no frames to pop.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot pop on empty frame stack
     */
    public function testPopEmpty()
    {
        $this->stack->pop();
    }

    /**
     * It should return the top frame.
     */
    public function testTop()
    {
        $frame = $this->stack->spawn();
        $topFrame = $this->stack->top();
        $this->assertSame($frame, $topFrame);
    }

    /**
     * It should spawn a frame with variables from the current frame.
     */
    public function testSpawnWith()
    {
        $frame = $this->stack->spawn();
        $frame->set('foobar', new Variable('asd'));
        $frame->set('barfoo', new Variable('asd'));
        $newFrame = $this->stack->spawnWith(['foobar']);
        $this->assertEquals([ 'foobar' ], $newFrame->keys());
    }
}

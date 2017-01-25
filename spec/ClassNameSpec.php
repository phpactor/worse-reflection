<?php

namespace spec\DTL\WorseReflection;

use DTL\WorseReflection\ClassName;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClassNameSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ClassName::class);
    }
}

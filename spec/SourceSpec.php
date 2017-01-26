<?php

namespace spec\DTL\WorseReflection;

use DTL\WorseReflection\Source;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SourceSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Source::class);
    }

    function it_should_be_created_from_a_filepath()
    {
        $this::fromFilepath(__FILE__)->shouldReturnAnInstanceOf(Source::class);
    }
}

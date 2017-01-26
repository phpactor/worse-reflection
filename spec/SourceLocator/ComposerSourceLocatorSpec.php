<?php

namespace spec\DTL\WorseReflection\SourceLocator;

use DTL\WorseReflection\Source;
use DTL\WorseReflection\SourceLocator\ComposerSourceLocator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DTL\WorseReflection\ClassName;

class ComposerSourceLocatorSpec extends ObjectBehavior
{
    private $classLoader;

    function let()
    {
        $this->classLoader = require(__DIR__ . '/../../vendor/autoload.php');
        $this->beConstructedWith($this->classLoader);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComposerSourceLocator::class);
    }

    function it_should_locate_a_class()
    {
        $classFqn = ComposerSourceLocator::class;

        $this->locate(ClassName::create($classFqn))->shouldReturnAnInstanceOf(Source::class);
    }
}

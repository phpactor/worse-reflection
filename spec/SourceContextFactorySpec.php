<?php

namespace spec\DTL\WorseReflection;

use DTL\WorseReflection\SourceContextFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DTL\WorseReflection\Source;
use PhpParser\ParserFactory;
use DTL\WorseReflection\SourceContext;

class SourceContextFactorySpec extends ObjectBehavior
{
    function let()
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->beConstructedWith($parser);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SourceContextFactory::class);
    }

    function it_should_return_a_source_context()
    {
        $this->createFor(Source::fromString('<?php // hello'))
            ->shouldReturnAnInstanceOf(SourceContext::class);
    }
}

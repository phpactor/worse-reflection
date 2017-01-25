<?php

namespace spec\DTL\WorseReflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContextFactory;
use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\Reflection\ReflectionClass;
use DTL\WorseReflection\ClassLocator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PhpParser\ParserFactory;
use DTL\WorseReflection\ClassName;

class ReflectorSpec extends ObjectBehavior
{
    function let(
        ClassLocator $classLocator,
        SourceContextFactory $sourceContextFactory
    )
    {
        $this->beConstructedWith($classLocator, $sourceContextFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Reflector::class);
    }

    function it_should_reflect_a_class(
        ClassLocator $classLocator,
        ReflectionClass $reflectionClass,
        Source $source,
        SourceContextFactory $sourceContextFactory,
        SourceContext $sourceContext
    )
    {
        $className = ClassName::create(ExampleClass::class);
        $classLocator->locate($className)->willReturn($source);
        $sourceContextFactory->hasClass($className)->willReturn(true);
        $sourceContextFactory->createFor($source)->willReturn($sourceContext);

        $this->reflectClass($className)->shouldBeLike(
            new ReflectionClass(ExampleClass::class)
        );
    }
}

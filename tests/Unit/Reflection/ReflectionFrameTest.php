<?php

namespace DTL\WorseReflection\Tests\Unit\Reflection;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\NodeDispatcher;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\Node;
use DTL\WorseReflection\Frame\Visitor\FrameFinderVisitor;
use DTL\WorseReflection\Type;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\SourceLocator\StringSourceLocator;
use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContextFactory;
use DTL\WorseReflection\Reflection\ReflectionFrame;
use DTL\WorseReflection\ClassName;

class ReflectionFrameTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionFrame
     */
    public function testReflectionFrame(string $source, array $expectedVariables)
    {
        list($offset, $source) = $this->getOffsetAndSource($source);

        $reflector = $this->getReflectorForSource($source);
        $offset = $reflector->reflectOffsetInSource($offset, $source);
        $this->assertEquals(
            $expectedVariables, $offset->getFrame()->all()
        );
    }

    public function provideReflectionFrame()
    {
        return [
            [
                '$foobar_ = "hello";',
                [
                    'foobar' => Type::string(),
                ],
            ],
            [
                <<<'EOT'
class Barbar
{
}

class Foobar
{
    public function hello()
    {
        $bar = $this->world();_
    }

    public function world(): Barbar
    {
    }
}
EOT
                , [
                    'bar' => Type::class(ClassName::fromString('Barbar')),
                    'this' => Type::class(ClassName::fromString('Foobar')),
                ],
            ],
            [
                <<<'EOT'
class BarFoo
{
}

class Barbar
{
    public function foobar(): Barfoo
    {
    }
}

class Foobar
{
    public function hello()
    {
        $bar = $this->world()->foobar();_
    }

    public function world(): Barbar
    {
    }
}
EOT
                , [
                    'bar' => Type::class(ClassName::fromString('Barfoo')),
                    'this' => Type::class(ClassName::fromString('Foobar')),
                ],
            ],
            [
                <<<'EOT'
class Class1
{
}

class Class2
{
    public function getClass1(): Class1
    {
    }

    public function execute()
    {
        $this->getClass1();_
    }
}
EOT
                , [
                ],
            ],
        ];
    }
}

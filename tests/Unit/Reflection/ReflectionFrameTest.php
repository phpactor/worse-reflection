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
        $offset = strpos($source, '_');

        if (false !== $offset) {
            $source = substr($source, 0, $offset) . substr($source, $offset + 1);
        }

        $source = Source::fromString('<?php ' . $source);
        $reflector = $this->getReflectorForSource($source);
        $frame = $reflector->reflectFrame($source, $offset);
        $this->assertEquals(
            $expectedVariables, $frame->all()
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
        ];
    }
}

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

class ReflectionOffsetTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionFrame
     */
    public function testReflectionFrame(string $source, array $expectedVariables, Type $expectedType)
    {
        list($offset, $source) = $this->getOffsetAndSource($source);

        $reflector = $this->getReflectorForSource($source);
        $offset = $reflector->reflectOffsetInSource($offset, $source);

        $this->assertEquals(
            $expectedVariables, $offset->getFrame()->all()
        );

        $this->assertEquals(
            $expectedType,
            $offset->getType()
        );
    }

    public function provideReflectionFrame()
    {
        return [
            'simpleAssignment' => [
                '$fooba_r = "hello";',
                [
                    'foobar' => Type::string(),
                ],
                Type::string(),
            ],
            'methodScope1' => [
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
                Type::none(),
            ],
            'methodScopeChained' => [
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
                Type::none(),
            ],
            'methodScopeType' => [
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
        $bar = $this->world()->fo_obar();
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
                Type::class(ClassName::fromString('Barfoo')),
            ],
        ];
    }
}

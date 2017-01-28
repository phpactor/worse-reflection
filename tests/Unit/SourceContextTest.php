<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflection\ReflectionClass;
use PhpParser\Node\Stmt\Class_;

class SourceContextTest extends IntegrationTestCase
{
    /**
     * It can say if it has a class.
     *
     * @dataProvider provideGetClassNode
     */
    public function testGetClassNode($className, $filename)
    {
        $context = $this->createContext($filename);
        $class = $context->getClassNode(ClassName::fromFqn($className));
        $this->assertInstanceOf(Class_::class, $class);
    }

    public function provideGetClassNode()
    {
        return [
            [
                'ClassOne',
                'GetClass.php',
            ],
            [
                'ClassTwo',
                'GetClass.php',
            ],
            [
                'ClassTwo',
                'GetClass.Namespaced.php',
            ],
        ];
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMethod Source context does not contain class
     */
    public function testGetClassNotFound()
    {
        $context = $this->createContext('GetClass.php');
        $context->getClassNode(ClassName::fromFqn('FoobarBarfoo132'));
    }

    private function createContext($filename)
    {
        $source = Source::fromString(file_get_contents(__DIR__ . '/SourceContext/' . $filename));
        return new SourceContext($source, $this->getParser());
    }
}

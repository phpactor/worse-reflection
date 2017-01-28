<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflection\ReflectionClass;
use PhpParser\Node\Stmt\Class_;
use DTL\WorseReflection\Namespace_;

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

    /**
     * It should return the namespace.
     *
     * @dataProvider provideGetNamespace
     */
    public function testGetNamespace($expectedNamespace, $filename)
    {
        $context = $this->createContext($filename);
        $namespace = $context->getNamespace();
        $this->assertInstanceOf(Namespace_::class, $namespace);
        $this->assertEquals($expectedNamespace, $namespace->getFqn());
    }

    public function provideGetNamespace()
    {
        return [
            [
                'DTL\WorseReflection\Tests\Unit\Example',
                'GetClass.Namespaced.php',
            ],
        ];
    }

    /**
     * It should return the imported use statements.
     *
     * @dataProvider provideResolveClassName
     */
    public function testResolveClassName($collaborator, $expectedClassFqn, $filename)
    {
        $context = $this->createContext($filename);
        $className = $context->resolveClassName($collaborator);
        $this->assertInstanceOf(ClassName::class, $className);
        $this->assertEquals($expectedClassFqn, $className->getFqn());
    }

    public function provideResolveClassName()
    {
        return [
            [
                'ColaboratorOne',
                'Foobar\Barfoo\ColaboratorOne',
                'ResolveClassName.php',
            ],
            [
                'AliasedColaboratorTwo',
                'Foobar\Barfoo\ColaboratorTwo',
                'ResolveClassName.php',
            ],
            [
                'MultiUseColaboratorThree',
                'Foobar\Barfoo\Baz\MultiUseColaboratorThree',
                'ResolveClassName.php',
            ],
            [
                'AliasedMultiUseColaboratorFour',
                'Foobar\Barfoo\Baz\MultiUseColaboratorFour',
                'ResolveClassName.php',
            ],
            [
                'SameScope',
                'DTL\WorseReflection\Tests\Unit\SourceContext\SameScope',
                'ResolveClassName.php',
            ],

        ];
    }

    private function createContext($filename)
    {
        $source = Source::fromString(file_get_contents(__DIR__ . '/SourceContext/' . $filename));

        return new SourceContext($source, $this->getParser());
    }
}

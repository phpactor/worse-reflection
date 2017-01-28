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
     * @dataProvider provideGetClass
     */
    public function testGetClass($className, $filename)
    {
        $context = $this->createContext($filename);
        $class = $context->getClassNode(ClassName::fromFqn($className));
        $this->assertInstanceOf(Class_::class, $class);
    }

    public function provideGetClass()
    {
        return [
            [
                'ClassOne',
                'GetClass.1.php',
            ],
            [
                'ClassTwo',
                'GetClass.1.php',
            ],
        ];
    }

    private function createContext($filename)
    {
        $source = Source::fromString(file_get_contents(__DIR__ . '/SourceContext/' . $filename));
        return new SourceContext($source, $this->getParser());
    }
}

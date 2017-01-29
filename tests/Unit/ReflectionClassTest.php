<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Source;

class ReflectionClassTest extends IntegrationTestCase
{
    /**
     * It return the interface names
     *
     * @dataProvider provideReturnInterfaceNames
     */
    public function testReturnInterfaceNames(string $className, string $source, array $expectedInterfaceNames)
    {
        $class = $this->reflectClassFromSource($className, $source);
        $interfaceReflections = $class->getInterfaces();

        $this->assertCount(count($expectedInterfaceNames), $interfaceReflections);

        foreach ($expectedInterfaceNames as $interfaceName) {
            $interfaceReflection = array_shift($interfaceReflections);
            $this->assertEquals($interfaceName, $interfaceReflection->getName());
        }
    }

    public function provideReturnInterfaceNames()
    {
        return [
            [
                'Foobar',
                <<<EOT
<?php 

interface FoobarInterface
{
}

class Foobar implements FoobarInterface
{
}
EOT
                ,
                [ ClassName::fromFqn('FoobarInterface') ],
            ],
            [
                'Foobar',
                <<<EOT
<?php 

interface FoobarInterface
{
}

interface BarfooInterface
{
}

class Foobar implements FoobarInterface, BarfooInterface
{
}
EOT
                ,
                [ ClassName::fromFqn('FoobarInterface'), ClassName::fromFqn('BarfooInterface'), ],
            ]
        ];
    }

    /**
     * It return the constants.
     */
    public function testConstants()
    {
        $this->markTestIncomplete();
    }

    /**
     * It return the doc comment.
     */
    public function testDocComment()
    {
        $this->markTestIncomplete();
    }

    /**
     * It returns the parent class
     */
    public function testParentClass()
    {
        $this->markTestIncomplete();
    }

    /**
     * It returns the properties.
     */
    public function testProperties()
    {
        $this->markTestIncomplete();
    }

    /**
     * It return the class name.
     *
     * @dataProvider provideGetClassName
     */
    public function testGetName(string $className, string $source, ClassName $expectedClassName)
    {
        $class = $this->reflectClassFromSource($className, $source);
        $this->assertEquals($expectedClassName, $class->getName());
    }

    public function provideGetClassName()
    {
        return [
            [
                'Foobar',
                <<<EOT
<?php 

class Foobar {
}
EOT
            ,
            ClassName::fromFqn('Foobar'),
            ],
            [
                'Foobar',
                <<<EOT
<?php 

namespace Foobar\Barfoo;

class Foobar {
}
EOT
            ,
            ClassName::fromParts([ 'Foobar', 'Barfoo', 'Foobar' ]),
            ]
        ];
    }

    /**
     * It reflect methods.
     *
     * @dataProvider provideReflectMethods
     */
    public function testReflectMethods(string $className, string $source, array $expectedMethods)
    {
        $class = $this->reflectClassFromSource($className, $source);
        $methods = $class->getMethods();
        $this->assertCount(1, $methods);
        $methodOne = $methods->current();
        $this->assertEquals('methodOne', $methodOne->getName());
        $this->assertTrue($methodOne->getVisibility()->isPublic());
    }

    public function provideReflectMethods()
    {
        return [
            [
                'Foobar',
                <<<EOT
<?php 

class Foobar {
public function methodOne() {
}
}
EOT
                ,
                [
                    'methodOne'
                ]
            ]
        ];
    }

    private function reflectClassFromSource(string $className, string $source)
    {
        return $this->getReflectorForSource(Source::fromString($source))->reflectClass(
            ClassName::fromFqn($className),
            Source::fromString($source)
        );
    }
}

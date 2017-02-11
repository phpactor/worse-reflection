<?php

namespace DTL\WorseReflection\Tests\Unit\Reflection;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\Visibility;

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
                [ ClassName::fromString('FoobarInterface') ],
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
                [ ClassName::fromString('FoobarInterface'), ClassName::fromString('BarfooInterface'), ],
            ]
        ];
    }

    /**
     * It return the constants.
     *
     * @dataProvider provideConstants
     */
    public function testConstants(string $className, string $source, array $expectedNames)
    {
        $class = $this->reflectClassFromSource($className, $source);
        $constants = $class->getConstants()->all();

        $this->assertCount(count($expectedNames), $constants);

        foreach ($expectedNames as $expectedName => $expectedValue) {
            $constant = array_shift($constants);
            $this->assertEquals($expectedName, $constant->getName());
            $this->assertEquals($expectedValue, $constant->getValue());
        }
    }

    public function provideConstants()
    {
        return [
            [
                'Foobar',
                <<<EOT
<?php 

class Foobar
{
    const TWELVE = 12;
    const FOOBAR = 'barfoo';
}
EOT
                ,
                [
                    'TWELVE' => 12,
                    'FOOBAR' => 'barfoo',
                ]
            ],
        ];
    }

    /**
     * It return the doc comment.
     */
    public function testDocComment()
    {
        $source = <<<EOT
<?php

/**
 * This is a comment.
 */
class Foobar
{
}
EOT
        ;
        $class = $this->reflectClassFromSource('Foobar', $source);
        $this->assertEquals(<<<EOT
/**
 * This is a comment.
 */
EOT
        , $class->getDocComment()->getRaw());
    }

    /**
     * It returns the parent class
     */
    public function testParentClass()
    {
        $source = <<<EOT
<?php

class ParentClass
{
}

class Foobar extends ParentClass
{
}
EOT
        ;
        $class = $this->reflectClassFromSource('Foobar', $source);
        $parentClass = $class->getParentClass();
        $this->assertEquals('ParentClass', $parentClass->getName()->getFqn());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Class "Foobar" has no parent
     */
    public function testGetParentClassNoParent()
    {
        $source = <<<EOT
<?php

class Foobar
{
}
EOT
        ;
        $class = $this->reflectClassFromSource('Foobar', $source);
        $class->getParentClass();
    }

    /**
     * It returns the properties.
     *
     * @dataProvider provideProperties
     */
    public function testProperties(string $className, string $source, array $expectedProperties)
    {
        $class = $this->reflectClassFromSource('Foobar', $source);
        $properties = $class->getProperties();

        $this->assertCount(count($expectedProperties), $properties);

        foreach ($expectedProperties as $expectedName => $expected) {
            $propertyReflection = array_shift($properties);
            $this->assertEquals($expectedName, $propertyReflection->getName());
            $this->assertEquals($expected['visibility'], $propertyReflection->getVisibility());
        }
    }

    public function provideProperties()
    {
        return [
            [
                'Foobar',
                <<<'EOT'
<?php 

class Foobar
{
    private $private = 'default1';
    protected $protected;
    public $public;
}
EOT
                ,
                [
                    'private' => [
                        'visibility' => Visibility::private(),
                    ],
                    'protected' => [
                        'visibility' => Visibility::protected()
                    ],
                    'public' => [
                        'visibility' => Visibility::public(),
                    ],
                ]
            ],
        ];
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
            ClassName::fromString('Foobar'),
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
        $methodOne = $methods->getIterator()->current();
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
            ClassName::fromString($className),
            Source::fromString($source)
        );
    }
}

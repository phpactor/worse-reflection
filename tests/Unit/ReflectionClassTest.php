<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Source;

class ReflectionClassTest extends IntegrationTestCase
{
    /**
     * It should return the class name.
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
     * It should reflect methods.
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
        return $this->getReflector()->reflectClassFromSource(
            ClassName::fromFqn($className),
            Source::fromString($source)
        );
    }
}

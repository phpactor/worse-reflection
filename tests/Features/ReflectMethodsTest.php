<?php

namespace DTL\WorseReflection\Tests\Features;

use DTL\WorseReflection\SourceContextFactory;
use DTL\WorseReflection\SourceLocator\ComposerSourceLocator;
use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceLocator;
use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Source;

class ReflectMethodsTest extends IntegrationTestCase
{
    /**
     * It should reflect methods.
     *
     * @dataProvider provideReflectMethods
     */
    public function testReflectMethods(string $className, string $source, array $expectedMethods)
    {
        $class = $this->getReflector()->reflectClassFromSource(
            ClassName::fromFqn($className),
            Source::fromString($source)
        );
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
}

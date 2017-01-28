<?php

namespace DTL\WorseReflection\Tests\Features;

use DTL\WorseReflection\SourceContextFactory;
use DTL\WorseReflection\SourceLocator\ComposerSourceLocator;
use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceLocator;
use DTL\WorseReflection\Tests\IntegrationTestCase;

class ReflectMethodsTest extends IntegrationTestCase
{
    /**
     * It should reflect methods.
     *
     * @dataProvider provideReflectMethods
     */
    public function testReflectMethods(string $className, string $source, array $expectedMethods)
    {
        $context = $this->getReflector()->reflectString($source);
        $class = $context->getClass($className);
        $methods = $class->getMethods();
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

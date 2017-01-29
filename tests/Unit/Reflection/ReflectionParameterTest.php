<?php

namespace DTL\WorseReflection\Tests\Unit\Reflection;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Reflection\ReflectionParameter;

class ReflectionParameterTest extends IntegrationTestCase
{
    /**
     * It returns the parameter name.
     */
    public function testParameterName()
    {
        $parameters = $this->getParametersFromString('$paramOne');
        $parameter = $parameters->get('paramOne');
        $this->assertEquals('paramOne', $parameter->getName());
    }

    /**
     * It should return the parameter type.
     */
    public function testType()
    {
        $this->markTestIncomplete();
    }

    /**
     * It should return the default value.
     */
    public function testDefaultValue()
    {
        $this->markTestIncomplete();
    }

    /**
     * It should return the index.
     */
    public function testIndex()
    {
        $this->markTestIncomplete();
    }

    private function getParametersFromString(string $params)
    {
        $source = <<<EOT
<?php

class Foobar
{
    public function method($params)
{
}
}
EOT
        ;
        $class = $this->getReflectorForSource(Source::fromString($source))->reflectClass(
            ClassName::fromFqn('Foobar'),
            Source::fromString($source)
        );

        return $class->getMethods()->get('method')->getParameters();
    }
}

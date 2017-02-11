<?php

namespace DTL\WorseReflection\Tests\Unit\Reflection;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Reflection\ReflectionParameter;
use DTL\WorseReflection\Type;

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
     *
     * @dataProvider provideType
     */
    public function testType($type)
    {
        $parameters = $this->getParametersFromString($type . ' $paramOne');
        $parameter = $parameters->get('paramOne');
        $this->assertEquals(Type::$type(), $parameter->getType());
    }

    public function provideType()
    {
        return [
            [
                'string',
            ],
            [
                'int',
            ],
            [
                'float',
            ]
        ];
    }

    public function testObjectType()
    {
        $parameters = $this->getParametersFromString('Collaborator $foobar');
        $parameter = $parameters->get('foobar');
        $this->assertEquals(Type::class(ClassName::fromString('Collaborator')), $parameter->getType());
    }

    /**
     * It should return the default value.
     */
    public function testDefaultValue()
    {
        $parameters = $this->getParametersFromString('$foobar = "barfoo"');
        $parameter = $parameters->get('foobar');
        $this->assertEquals('barfoo', $parameter->getDefault());
    }

    /**
     * It should return null as default value.
     */
    public function testDefaultValueNull()
    {
        $parameters = $this->getParametersFromString('$foobar');
        $parameter = $parameters->get('foobar');
        $this->assertNull($parameter->getDefault());
    }

    /**
     * It should return the default value for a constant
     */
    public function testDefaultValueFromConstant()
    {
        $parameters = $this->getParametersFromString('$foobar = Collaborator::BARFOO');
        $parameter = $parameters->get('foobar');
        $this->assertEquals('foobar', $parameter->getDefault());
    }

    private function getParametersFromString(string $params)
    {
        $source = <<<EOT
<?php

class Collaborator
{
    const BARFOO = 'foobar';
}

class Foobar
{
    public function method($params)
{
}
}
EOT
        ;
        $class = $this->getReflectorForSource(Source::fromString($source))->reflectClass(
            ClassName::fromString('Foobar'),
            Source::fromString($source)
        );

        return $class->getMethods()->get('method')->getParameters();
    }
}

<?php

namespace DTL\WorseReflection\Tests\Unit\Reflection;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Reflection\ReflectionParameter;

class ReflectionMethodTest extends IntegrationTestCase
{
    /**
     * It returns parameters
     */
    public function testParameters()
    {
        $source = <<<'EOT'
<?php

class Foobar
{
    public function noParameters()
    {
    }

    public function singleParameters($foobar)
    {
    }

    public function multipleParameters($foobar, $barfoo)
    {
    }
}
EOT
        ;
        $reflector = $this->getReflectorForSource(Source::fromString($source));
        $class = $reflector->reflectClass(ClassName::fromString('Foobar'));

        $parameters = $class->getMethods()->get('noParameters')->getParameters();
        $this->assertCount(0, $parameters->all());

        $parameters = $class->getMethods()->get('singleParameters')->getParameters();
        $this->assertCount(1, $parameters->all());
        $this->assertContainsOnlyInstancesOf(ReflectionParameter::class, $parameters);

        $parameters = $class->getMethods()->get('multipleParameters')->getParameters();
        $this->assertCount(2, $parameters->all());
        $this->assertContainsOnlyInstancesOf(ReflectionParameter::class, $parameters);
    }

    /**
     * It returns visibility.
     */
    public function testVisibility()
    {
        $source = <<<EOT
<?php

class Foobar
{
    function method()
    {
    }

    public function publicM()
    {
    }

    protected function protectedM()
    {
    }

    private function privateM()
    {
    }
}
EOT
        ;

        $reflector = $this->getReflectorForSource(Source::fromString($source));
        $class = $reflector->reflectClass(ClassName::fromString('Foobar'));

        $this->assertEquals(
            Visibility::public(),
            $class->getMethods()->get('method')->getVisibility()
        );

        $this->assertEquals(
            Visibility::public(),
            $class->getMethods()->get('publicM')->getVisibility()
        );
        $this->assertEquals(
            Visibility::protected(),
            $class->getMethods()->get('protectedM')->getVisibility()
        );
        $this->assertEquals(
            Visibility::private(),
            $class->getMethods()->get('privateM')->getVisibility()
        );
    }

    /**
     * It returns variables.
     */
    public function testVariables()
    {
        $source = <<<'EOT'
<?php

class Foobar
{
    function method()
    {
        $foo = 'bar';

        if ($foo == 'boo') {
            $bar = $foo; // in scope
        }

        function () {
            $zed = 'zog'; // out of scope
        };
    }

    public function publicM()
    {
        $baz = 'daz';
    }
}
EOT
        ;

        $reflector = $this->getReflectorForSource(Source::fromString($source));
        $class = $reflector->reflectClass(ClassName::fromString('Foobar'));
        $method = $class->getMethods()->get('method');
        $variables = iterator_to_array($method->getVariables());
        $this->assertCount(2, $variables);
        $this->assertEquals('foo', $variables[0]->getName());
        $this->assertEquals('bar', $variables[1]->getName());
    }
}

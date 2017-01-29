<?php

namespace DTL\WorseReflection\Tests\Unit\Reflection;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Visibility;

class ReflectionMethodTest extends IntegrationTestCase
{
    /**
     * It should return visibility.
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
        $class = $reflector->reflectClass(ClassName::fromFqn('Foobar'));

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

}

<?php

namespace DTL\WorseReflection\Tests\Reflection;

use DTL\WorseReflection\Tests\ReflectionTestCase;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflection\AbstractReflectionMethod;
use DTL\WorseReflection\Reflection\ReflectionMethod;
use DTL\WorseReflection\Reflection\ReflectionInterface;
use DTL\WorseReflection\Visibility;

class ReflectionMethodTest extends ReflectionTestCase
{
    /**
     * @dataProvider provideReflectionMethod
     */
    public function testReflectMethod(string $source, string $class, string $method, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString($class));
        $method = $reflectionMethod = $class->methods()->get($method);
        $assertion($method);
    }

    public function provideReflectionMethod()
    {
        return [
            'It reflects a method' => [
                <<<'EOT'
<?php

class Foobar
{
    public function method();
}
EOT
                ,
                'Foobar',
                'method',
                function ($method) {
                    $this->assertEquals('method', $method->name());
                    $this->assertInstanceOf(ReflectionMethod::class, $method);
                }
            ],
            'Private visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    private function privateMethod();
}
EOT
                ,
                'Foobar',
                'privateMethod',
                function ($method) {
                    $this->assertEquals(Visibility::private(), $method->visibility());
                }
            ],
        ];
    }
}

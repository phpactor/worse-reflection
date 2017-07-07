<?php

namespace Phpactor\WorseReflection\Tests\Integration\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Visibility;
use Phpactor\WorseReflection\Type;

class ReflectionMethodTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionMethod
     */
    public function testReflectMethod(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString($class));
        $assertion($class->methods());
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
                function ($methods) {
                    $this->assertEquals('method', $methods->get('method')->name());
                    $this->assertInstanceOf(ReflectionMethod::class, $methods->get('method'));
                },
            ],
            'Private visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    private function method();
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertEquals(Visibility::private(), $methods->get('method')->visibility());
                },
            ],
            'Protected visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    protected function method()
    {
    }
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertEquals(Visibility::protected(), $methods->get('method')->visibility());
                },
            ],
            'Public visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    public function method();
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertEquals(Visibility::public(), $methods->get('method')->visibility());
                },
            ],
            'Return type' => [
                <<<'EOT'
<?php

use Acme\Post;

class Foobar
{
    function method1(): int {}
    function method2(): string {}
    function method3(): float {}
    function method4(): array {}
    function method5(): Barfoo {}
    function method6(): Post {}
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertEquals(Type::int(), $methods->get('method1')->type());
                    $this->assertEquals(Type::string(), $methods->get('method2')->type());
                    $this->assertEquals(Type::float(), $methods->get('method3')->type());
                    $this->assertEquals(Type::array(), $methods->get('method4')->type());
                    $this->assertEquals(Type::class(ClassName::fromString('Barfoo')), $methods->get('method5')->type());
                    $this->assertEquals(Type::class(ClassName::fromString('Acme\Post')), $methods->get('method6')->type());
                },
            ],
            'Inherited methods' => [
                <<<'EOT'
<?php

class ParentParentClass extends NonExisting
{
    public function method5() {}
}

class ParentClass extends ParentParentClass
{
    private function method1() {}
    protected function method2() {}
    public function method3() {}
    public function method4() {}
}

class Foobar extends ParentClass
{
    public function method4() {} // overrides from previous
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertEquals(
                        ['method5', 'method2', 'method3', 'method4'],
                        $methods->keys()
                    );
                },
            ],
            'Return type from docblock' => [
                <<<'EOT'
<?php

use Acme\Post;

class Foobar
{
    /**
     * @return Post
     */
    function method1() {}
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertEquals(Type::class(ClassName::fromString('Acme\Post')), $methods->get('method1')->type());
                },
            ],
            'Return type from inherited docblock' => [
                <<<'EOT'
<?php

use Acme\Post;

class ParentClass
{
    /**
     * @return \Articles\Blog
     */
    function method1() {}
}

class Foobar extends ParentClass
{
    /**
     * {@inheritdoc}
     */
    function method1() {}
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertEquals(Type::class(ClassName::fromString('Articles\Blog')), $methods->get('method1')->type());
                },
            ],
            'It reflects an abstract method' => [
                <<<'EOT'
<?php

abstract class Foobar
{
    abstract public function method();
    public function methodNonAbstract();
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertTrue($methods->get('method')->isAbstract());
                    $this->assertFalse($methods->get('methodNonAbstract')->isAbstract());
                },
            ],
            'It returns the method parameters' => [
                <<<'EOT'
<?php

class Foobar
{
    public function barfoo($foobar, Barfoo $barfoo, int $number)
    {
    }
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertCount(3, $methods->get('barfoo')->parameters());
                },
            ],
            'It returns the raw docblock' => [
                <<<'EOT'
<?php

class Foobar
{
    /**
     * Hello this is a docblock.
     */
    public function barfoo($foobar, Barfoo $barfoo, int $number)
    {
    }
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertContains(<<<EOT
Hello this is a docblock.
EOT
                    , $methods->get('barfoo')->docblock()->raw());
                },
            ],
            'It returns the formatted docblock' => [
                <<<'EOT'
<?php

class Foobar
{
    /**
     * Hello this is a docblock.
     *
     * Yes?
     */
    public function barfoo($foobar, Barfoo $barfoo, int $number)
    {
    }
}
EOT
                ,
                'Foobar',
                function ($methods) {
                    $this->assertEquals(<<<EOT
Hello this is a docblock.

Yes?
EOT
                    , $methods->get('barfoo')->docblock()->formatted());
                },
            ]
        ];
    }
}

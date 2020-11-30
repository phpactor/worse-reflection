<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Generator;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;

class ReflectionPropertyTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionProperty
     * @dataProvider provideConsturctorPropertyPromotion
     */
    public function testReflectProperty(string $source, string $class, \Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class->properties());
    }

    public function provideReflectionProperty()
    {
        yield 'It reflects a property' => [
                <<<'EOT'
<?php

class Foobar
{
    public $property;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals('property', $properties->get('property')->name());
                    $this->assertInstanceOf(ReflectionProperty::class, $properties->get('property'));
                },
            ];

        yield 'Private visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    private $property;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(Visibility::private(), $properties->get('property')->visibility());
                },
            ];

        yield 'Protected visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    protected $property;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(Visibility::protected(), $properties->get('property')->visibility());
                },
            ];

        yield 'Public visibility' => [
                <<<'EOT'
<?php

class Foobar
{
    public $property;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(Visibility::public(), $properties->get('property')->visibility());
                },
            ];

        yield 'Inherited properties' => [
                <<<'EOT'
<?php

class ParentParentClass extends NonExisting
{
    public $property5;
}

class ParentClass extends ParentParentClass
{
    private $property1;
    protected $property2;
    public $property3;
    public $property4;
}

class Foobar extends ParentClass
{
    public $property4; // overrides from previous
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(
                        ['property5', 'property2', 'property3', 'property4'],
                        $properties->keys()
                    );
                },
            ];

        yield 'Return type from docblock' => [
                <<<'EOT'
<?php

use Acme\Post;

class Foobar
{
    /**
     * @var Post
     */
    private $property1;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(
                        Type::class(ClassName::fromString('Acme\Post')),
                        $properties->get('property1')->inferredTypes()->best()
                    );
                    $this->assertFalse($properties->get('property1')->isStatic());
                },
            ];

        yield 'Returns unknown type for (real) type' => [
                <<<'EOT'
<?php

use Acme\Post;

class Foobar
{
    /**
     * @var Post
     */
    private $property1;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals(
                        Type::unknown(),
                        $properties->get('property1')->type()
                    );
                },
            ];

        yield 'Property with assignment' => [
                <<<'EOT'
<?php

use Acme\Post;

class Foobar
{
    private $property1 = 'bar';
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertTrue($properties->has('property1'));
                },
            ];

        yield 'Return true if property is static' => [
                <<<'EOT'
<?php

use Acme\Post;

class Foobar
{
    private static $property1;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertTrue($properties->get('property1')->isStatic());
                },
            ];

        yield 'Returns declaring class' => [
                <<<'EOT'
<?php

class Foobar
{
    private $property1;
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals('Foobar', $properties->get('property1')->declaringClass()->name()->__toString());
                },
            ];

        yield 'Property type from class @property annotation' => [
                <<<'EOT'
<?php

use Acme\Post;

/**
 * @property string $bar
 */
class Foobar
{
    private $bar;
}
EOT
                ,
                'Foobar',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(Type::fromString('string'), $properties->get('bar')->inferredTypes()->best());
                },
            ];

        yield 'Property type from class @property annotation with imported name' => [
                <<<'EOT'
<?php

use Acme\Post;
use Bar\Foo;

/**
 * @property Foo $bar
 */
class Foobar
{
    private $bar;
}
EOT
                ,
                'Foobar',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(Type::fromString('Bar\Foo'), $properties->get('bar')->inferredTypes()->best());
                },
            ];

        yield 'Property type from parent class @property annotation with imported name' => [
                <<<'EOT'
<?php

use Acme\Post;
use Bar\Foo;

/**
 * @property Foo $bar
 */
class Barfoo
{
    protected $bar;
}

class Foobar extends Barfoo
{
}
EOT
                ,
                'Foobar',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(Type::fromString('Bar\Foo'), $properties->get('bar')->inferredTypes()->best());
                },
            ];

        yield 'Typed property from imported class' => [
                <<<'EOT'
<?php

namespace Test;

use Acme\Post;
use Bar\Foo;

class Barfoo
{
     public Foo $bar;
     public string $baz;
     public $undefined;
     public iterable $it;

     /** @var Foo[] */
     public iterable $collection;
}
EOT
                ,
                'Test\Barfoo',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(Type::fromString('Bar\Foo'), $properties->get('bar')->type());
                    $this->assertEquals(Type::fromString('Bar\Foo'), $properties->get('bar')->inferredTypes()->best());

                    $this->assertEquals(Type::string(), $properties->get('baz')->type());

                    $this->assertEquals(Type::undefined(), $properties->get('undefined')->type());

                    $this->assertEquals(Type::iterable(), $properties->get('collection')->type());
                    $this->assertEquals(Type::array('Bar\Foo'), $properties->get('collection')->inferredTypes()->best());
                    $this->assertEquals(
                        Type::iterable(),
                        $properties->get('it')->type()
                    );
                },
            ];

        yield 'Nullable typed property' => [
                <<<'EOT'
<?php

namespace Test;

class Barfoo
{
     public ?string $foo;
}
EOT
                ,
                'Test\Barfoo',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(
                        Type::string()->asNullable(),
                        $properties->get('foo')->type()
                    );
                },
            ];
    }


    public function provideConsturctorPropertyPromotion(): Generator
    {
        yield 'Typed properties' => [
                <<<'EOT'
<?php

namespace Test;

class Barfoo
{
    public function __construct(
        private string $foobar
        private int $barfoo
    ) {}
}
EOT
                ,
                'Test\Barfoo',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertTrue($properties->get('foobar')->isPromoted());
                    $this->assertEquals(
                        Type::string(),
                        $properties->get('foobar')->type()
                    );
                    $this->assertEquals(
                        Type::int(),
                        $properties->get('barfoo')->type()
                    );
                },
            ];

        yield 'Nullable' => [
                '<?php class Barfoo { public function __construct(private ?string $foobar){}}',
                'Barfoo',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(
                        Type::string()->asNullable(),
                        $properties->get('foobar')->type()
                    );
                },
            ];

        yield 'No types' => [
                '<?php class Barfoo { public function __construct(private $foobar){}}',
                'Barfoo',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(
                        Type::undefined(),
                        $properties->get('foobar')->type()
                    );
                },
            ];
    }
}

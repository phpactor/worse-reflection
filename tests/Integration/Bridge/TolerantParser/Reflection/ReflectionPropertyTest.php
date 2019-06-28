<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

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
     */
    public function testReflectProperty(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class->properties());
    }

    public function provideReflectionProperty()
    {
        return [
            'It reflects a property' => [
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
            ],
            'Private visibility' => [
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
            ],
            'Protected visibility' => [
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
            ],
            'Public visibility' => [
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
            ],
            'Inherited properties' => [
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
            ],
            'Return type from docblock' => [
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
            ],
            'Returns unknown type for (real) type' => [
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
            ],
            'Property with assignment' => [
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
            ],
            'Return true if property is static' => [
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
            ],
            'Returns declaring class' => [
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
            ],
            'Property type from class @property annotation' => [
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
            ],
            'Property type from class @property annotation with imported name' => [
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
            ],
            'Property type from parent class @property annotation with imported name' => [
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
            ],
            'Understands root namespace' => [
                <<<'EOT'
<?php

class Foobar
{
    private $property1;

    public function __construct()
    {
        $this->property1 = new \DateTime();
    }
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals('\DateTime', $properties->get('property1')->inferredTypes()->best()->short());
                },
            ],
            'Does not print root backslash when asking for short version if multiple parts' => [
                <<<'EOT'
<?php

class Foobar
{
    private $property1;

    public function __construct()
    {
        $this->property1 = new \Some\Thing();
    }
}
EOT
                ,
                'Foobar',
                function ($properties) {
                    $this->assertEquals('Thing', $properties->get('property1')->inferredTypes()->best()->short());
                },
            ],
        ];
    }
}

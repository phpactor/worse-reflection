<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

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
                    $this->assertEquals(Type::class(ClassName::fromString('Acme\Post')), $properties->get('property1')->inferredTypes()->guess());
                    $this->assertFalse($properties->get('property1')->isStatic());
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
        ];
    }
}

<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;

class ReflectionClassTest extends IntegrationTestCase
{
    /**
     * @expectedException Phpactor\WorseReflection\Core\Exception\ClassNotFound
     */
    public function testExceptionOnClassNotFound()
    {
        $this->createReflector('')->reflectClassLike(ClassName::fromString('Foobar'));
    }

    /**
     * @dataProvider provideReflectionClass
     */
    public function testReflectClass(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    public function provideReflectionClass()
    {
        return [
            'It reflects an empty class' => [
                <<<'EOT'
<?php

class Foobar
{
}
EOT
                ,
                'Foobar',
                function ($class) {
                    $this->assertEquals('Foobar', (string) $class->name()->short());
                    $this->assertInstanceOf(ReflectionClass::class, $class);
                    $this->assertFalse($class->isInterface());
                },
            ],
            'It reflects a class which extends another' => [
                <<<'EOT'
<?php
class Barfoo
{
}

class Foobar extends Barfoo
{
}
EOT
                ,
                'Foobar',
                function ($class) {
                    $this->assertEquals('Foobar', (string) $class->name()->short());
                    $this->assertEquals('Barfoo', (string) $class->parent()->name()->short());
                },
            ],
            'It reflects class constants' => [
                <<<'EOT'
<?php

class Class1
{
    const EEEBAR = 'eeebar';
}

class Class2 extends Class1
{
    const FOOBAR = 'foobar';
    const BARFOO = 'barfoo';
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertCount(3, $class->constants());
                    $this->assertInstanceOf(ReflectionConstant::class, $class->constants()->get('FOOBAR'));
                    $this->assertInstanceOf(ReflectionConstant::class, $class->constants()->get('EEEBAR'));
                },
            ],
            'It can provide the name of its last member' => [
                <<<'EOT'
<?php

class Class2
{
    private $foo;
    private $bar;
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals('bar', $class->properties()->last()->name());
                },
            ],
            'It can provide the name of its first member' => [
                <<<'EOT'
<?php

class Class2
{
    private $foo;
    private $bar;
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals('foo', $class->properties()->first()->name());
                },
            ],
            'It can provide its position' => [
                <<<'EOT'
<?php

class Class2
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals(7, $class->position()->start());
                },
            ],
            'It can provide the position of its member declarations' => [
                <<<'EOT'
<?php

class Class2
{
    private $foobar;
    private $barfoo;

    public function zed()
    {
    }
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals(20, $class->memberListPosition()->start());
                },
            ],
            'It provides list of its interfaces' => [
                <<<'EOT'
<?php

interface InterfaceOne
{
}

class Class2 implements InterfaceOne
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals(1, $class->interfaces()->count());
                    $this->assertEquals('InterfaceOne', $class->interfaces()->first()->name());
                },
            ],
            'It list of interfaces includes interfaces from parent classes' => [
                <<<'EOT'
<?php

interface InterfaceOne
{
}

class Class1 implements InterfaceOne
{
}

class Class2 extends Class1
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals(1, $class->interfaces()->count());
                    $this->assertEquals('InterfaceOne', $class->interfaces()->first()->name());
                },
            ],
            'It provides list of its traits' => [
                <<<'EOT'
<?php

trait TraitNUMBERone
{
}

class Class2
{
    use TraitNUMBERone;
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals(1, $class->traits()->count());
                    $this->assertEquals('TraitNUMBERone', $class->traits()->first()->name());
                },
            ],
            'Traits are inherited from parent classes (?)' => [
                <<<'EOT'
<?php

trait TraitNUMBERone
{
}

class Class2
{
    use TraitNUMBERone;
}

class Class1 extends Class2
{
}

EOT
                ,
                'Class1',
                function ($class) {
                    $this->assertEquals(1, $class->traits()->count());
                    $this->assertEquals('TraitNUMBERone', $class->traits()->first()->name());
                },
            ],
            'Get methods includes trait methods' => [
                <<<'EOT'
<?php

trait TraitNUMBERone
{
    public function traitMethod()
    {
    }
}

class Class2
{
    use TraitNUMBERone;

    public function notATrait()
    {
    }
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals(2, $class->methods()->count());
                    $this->assertEquals('traitMethod', $class->methods()->first()->name());
                },
            ],
            'Get properties includes trait methods' => [
                <<<'EOT'
<?php

trait TraitNUMBERone
{
    public $foobar;
}

class Class2
{
    use TraitNUMBERone;

    private $notAFoobar;
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals(2, $class->properties()->count());
                    $this->assertEquals('foobar', $class->properties()->first()->name());
                },
            ],

            'If it extends an interface, then ignore' => [
                <<<'EOT'
<?php

interface SomeInterface
{
}

class Class2 extends SomeInterface
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertEquals(0, $class->methods()->count());
                },
            ],

            'isInstanceOf returns false when it is not an instance of' => [
                <<<'EOT'
<?php

class Class2
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertFalse($class->isInstanceOf(ClassName::fromString('Foobar')));
                },
            ],

            'isInstanceOf returns true for itself' => [
                <<<'EOT'
<?php

class Class2
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertTrue($class->isInstanceOf(ClassName::fromString('Class2')));
                },
            ],

            'isInstanceOf returns true when it is not an instance of an interface' => [
                <<<'EOT'
<?php

interface SomeInterface
{
}

class Class2 implements SomeInterface
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertTrue($class->isInstanceOf(ClassName::fromString('SomeInterface')));
                },
            ],

            'isInstanceOf returns true for a parent class' => [
                <<<'EOT'
<?php

class SomeParent
{
}

class Class2 extends SomeParent
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertTrue($class->isInstanceOf(ClassName::fromString('SomeParent')));
                },
            ],
            'Returns source code' => [
                <<<'EOT'
<?php

class Class2
{
}

EOT
                ,
                'Class2',
                function ($class) {
                    $this->assertContains('class Class2', (string) $class->sourceCode());
                },
            ],
        ];
    }
}

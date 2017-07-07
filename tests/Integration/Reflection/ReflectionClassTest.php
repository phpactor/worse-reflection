<?php

namespace Phpactor\WorseReflection\Tests\Integration\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Reflection\ReflectionConstant;

class ReflectionClassTest extends IntegrationTestCase
{
    /**
     * @expectedException Phpactor\WorseReflection\Exception\ClassNotFound
     */
    public function testExceptionOnClassNotFound()
    {
        $this->createReflector('')->reflectClass(ClassName::fromString('Foobar'));
    }

    /**
     * @dataProvider provideReflectionClass
     */
    public function testReflectClass(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString($class));
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
            'It reflects an interface' => [
                <<<'EOT'
<?php

interface Barfoo
{
}
EOT
                ,
                'Barfoo',
                function ($class) {
                    $this->assertEquals('Barfoo', (string) $class->name()->short());
                    $this->assertInstanceOf(ReflectionInterface::class, $class);
                },
            ],
            'It reflects a classes interfaces' => [
                <<<'EOT'
<?php
interface Barfoo
{
}

interface Bazbar
{
}

class Foobar implements Barfoo, Bazbar
{
}
EOT
                ,
                'Foobar',
                function ($class) {
                    $interfaces = $class->interfaces();
                    $this->assertCount(2, $interfaces);
                    $interface = $interfaces['Barfoo'];
                    $this->assertInstanceOf(ReflectionInterface::class, $interface);
                },
            ],
            'It reflects a class which implements an interface which extends other interfaces' => [
                <<<'EOT'
<?php
interface Barfoo
{
}

interface Zedboo
{
}

interface Bazbar extends Barfoo, Zedboo
{
}
EOT
                ,
                'Bazbar',
                function ($class) {
                    $interfaces = $class->parents();
                    $this->assertCount(2, $interfaces);
                    $interface = $interfaces['Barfoo'];
                    $this->assertInstanceOf(ReflectionInterface::class, $interface);
                },
            ],
            'It reflects inherited methods in an interface' => [
                <<<'EOT'
<?php
interface Barfoo
{
    public function methodOne();
}

interface Zedboo
{
    public function methodTwo();
}

interface Bazbar extends Barfoo, Zedboo
{
}
EOT
                ,
                'Bazbar',
                function ($interface) {
                    $this->assertInstanceOf(ReflectionInterface::class, $interface);
                    $this->assertCount(2, $interface->methods());
                },
            ],
            'It reflect interface methods' => [
                <<<'EOT'
<?php

interface Barfoo
{
    public function foobar();
}
EOT
                ,
                'Barfoo',
                function ($class) {
                    $this->assertEquals('Barfoo', (string) $class->name()->short());
                    $this->assertEquals(['foobar'], $class->methods()->keys());
                },
            ],
            'It interface constants' => [
                <<<'EOT'
<?php

interface Int1
{
    const FOOBAR = 'foobar';
}

interface Int2
{
    const FOOBAR = 'foobar';
    const BARFOO = 'barfoo';
}

interface Int3 extends Int1, Int2
{
    const EEEBAR = 'eeebar';
}
EOT
                ,
                'Int3',
                function ($class) {
                    $this->assertCount(3, $class->constants());
                    $this->assertInstanceOf(ReflectionConstant::class, $class->constants()->get('FOOBAR'));
                    $this->assertInstanceOf(ReflectionConstant::class, $class->constants()->get('EEEBAR'));
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
            'It can provide the position of its last member' => [
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
            ]
        ];
    }
}

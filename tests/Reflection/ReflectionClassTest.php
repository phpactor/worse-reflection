<?php

namespace DTL\WorseReflection\Tests\Reflection;

use DTL\WorseReflection\Tests\ReflectionTestCase;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflection\ReflectionClass;
use DTL\WorseReflection\Reflection\ReflectionInterface;

class ReflectionClassTest extends ReflectionTestCase
{
    /**
     * @expectedException DTL\WorseReflection\Exception\ClassNotFound
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
        ];
    }
}

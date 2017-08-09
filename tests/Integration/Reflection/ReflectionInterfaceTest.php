<?php

namespace Phpactor\WorseReflection\Tests\Integration\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Core\ClassName;

class ReflectionInterfaceTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionInterface
     */
    public function testReflectInterface(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString($class));
        $assertion($class);
    }

    public function provideReflectionInterface()
    {
        return [
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
                    $this->assertTrue($class->isInterface());
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
        ];
    }
}

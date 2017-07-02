<?php

namespace DTL\WorseReflection\Tests\Reflection;

use DTL\WorseReflection\Tests\ReflectionTestCase;

class ReflectionClassTest extends ReflectionTestCase
{
    public function provideReflectionClass()
    {
        return [
            'It reflects an empty class' => [
                <<<'EOT'
class Foobar
{
}
EOT
                ,
                'Foobar',
                function ($class) {
                    $this->assertEquals('Foobar', (string) $class->name()->short());
                    $this->assertTrue($class->isClass());
                    $this->assertFalse($class->isInterface());
                }
            ],
            'It reflects a class which extends another' => [
                <<<'EOT'
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
                }
            ],
            'It reflects an interface' => [
                <<<'EOT'
interface Barfoo
{
}
EOT
                ,
                'Barfoo',
                function ($class) {
                    $this->assertEquals('Barfoo', (string) $class->short());
                    $this->assertTrue($class->isInterface());
                    $this->assertFalse($class->isClass());
                }
            ],
        ];
    }
}

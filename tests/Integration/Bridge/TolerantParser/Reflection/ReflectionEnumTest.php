<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Closure;

class ReflectionEnumTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionEnum
     */
    public function testReflectEnum(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    public function provideReflectionEnum()
    {
        yield 'It reflects a enum' => [
                <<<'EOT'
                    <?php

                    enum Barfoo
                    {
                    }
    EOT
        ,
        'Barfoo',
        function ($class): void {
            $this->assertEquals('Barfoo', (string) $class->name()->short());
            $this->assertInstanceOf(ReflectionEnum::class, $class);
            $this->assertTrue($class->isEnum());
        },
            ];
    yield 'It reflect enum methods' => [
        <<<'EOT'
                    <?php

                    enum Barfoo
                    {
                        public function foobar()
                        {
                        }
                    }
    EOT
        ,
        'Barfoo',
        function ($class): void {
            $this->assertEquals('Barfoo', (string) $class->name()->short());
            $this->assertEquals(['foobar'], $class->methods()->keys());
        },
    ];
    yield 'Returns all members' => [
        <<<'EOT'
                <?php

                enum Class1
                {
                    private $foovar;
                    private function foobar() {}
                }

    EOT
        ,
        'Class1',
        function (ReflectionEnum $class): void {
            $this->assertCount(2, $class->members());
            $this->assertTrue($class->members()->has('foovar'));
            $this->assertTrue($class->members()->has('foobar'));
        },
    ];
    }
}

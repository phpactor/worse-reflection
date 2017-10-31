<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionSourceCode;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionNamespaceCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;

class ReflectionNamespaceTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionSourceCode
     */
    public function testReflectSourceCode(string $source, \Closure $assertion)
    {
        $source = $this->createReflector($source)->reflectSourceCode($source);
        $assertion($source->namespaces());
    }

    public function provideReflectionSourceCode()
    {
        return [
            'Returns classes in root namespace' => [
                <<<'EOT'
<?php
class Foobar
{
}
EOT
                ,
                function (ReflectionNamespaceCollection $namespaces) {
                    $this->assertCount(1, $namespaces);
                    $namespace = $namespaces->first();

                    $this->assertTrue($namespace->name()->isRoot());

                    $class = $namespaces->first()->classes()->first();
                    $this->assertInstanceOf(ReflectionClass::class, $class);
                    $this->assertEquals('Foobar', (string) $class->name());
                },
            ],
            'Returns classes in non-root namespace' => [
                <<<'EOT'
<?php

namespace Foobar;

class Foobar
{
}
EOT
                ,
                function (ReflectionNamespaceCollection $namespaces) {
                    $this->assertCount(1, $namespaces);
                    $class = $namespaces->first()->classes()->first();
                    $this->assertInstanceOf(ReflectionClass::class, $class);
                    $this->assertEquals('Foobar\\Foobar', (string) $class->name());
                },
            ],
            'Returns classes in additional namespace' => [
                <<<'EOT'
<?php

namespace Foobar {
    class Foobar {}
}

namespace Barfoo {
    class Barfoo {}
}
EOT
                ,
                function (ReflectionNamespaceCollection $namespaces) {
                    $this->assertCount(2, $namespaces);
                    $this->assertCount(1, $namespaces->get('Barfoo')->classes());
                },
            ],
        ];
    }
}

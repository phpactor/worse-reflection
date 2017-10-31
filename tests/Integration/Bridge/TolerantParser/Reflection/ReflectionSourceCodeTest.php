<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionSourceCode;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNamespace;

class ReflectionSourceCodeTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionSourceCode
     */
    public function testReflectSourceCode(string $source, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectSourceCode($source);
        $assertion($class);
    }

    public function provideReflectionSourceCode()
    {
        return [
            'It reflects source code' => [
                <<<'EOT'
<?php
EOT
                ,
                function (ReflectionSourceCode $class) {
                    $this->assertInstanceOf(ReflectionSourceCode::class, $class);
                },
            ],
            'It returns a root namespace' => [
                <<<'EOT'
<?php
EOT
                ,
                function (ReflectionSourceCode $source) {
                    $namespaces = $source->namespaces();
                    $this->assertCount(1, $namespaces);
                    $first = $namespaces->first();
                    $this->assertInstanceOf(ReflectionNamespace::class, $first);
                },
            ],
        ];
    }
}

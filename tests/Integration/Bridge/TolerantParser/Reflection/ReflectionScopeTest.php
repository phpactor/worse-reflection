<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Core\NameImports;

class ReflectionScopeTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideScope
     */
    public function testScope(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    public function provideScope()
    {
        yield 'Returns imported classes' => [
            <<<'EOT'
<?php

use Foobar\Barfoo;
use Barfoo\Foobaz as Carzatz;

class Class2
{
}

EOT
        ,
            'Class2',
            function ($class) {
                $this->assertEquals(NameImports::fromNames([
                    'Barfoo' => Name::fromString('Foobar\\Barfoo'),
                    'Carzatz' => Name::fromString('Barfoo\\Foobaz'),
                ]), $class->scope()->nameImports());
            },
        ];

        yield 'Returns local name' => [
            <<<'EOT'
<?php

use Foobar\Barfoo;

class Class2
{
}

EOT
        ,
            'Class2',
            function (ReflectionClass $class) {
                $this->assertEquals(
                    Name::fromString('Barfoo'),
                    $class->scope()->resolveLocalName(Name::fromString('Foobar\Barfoo'))
                );
            },
        ];
    }
}

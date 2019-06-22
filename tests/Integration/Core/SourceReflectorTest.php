<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\TestUtils\ExtractOffset;

class SourceReflectorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectClassNotCorrectType
     */
    public function testReflectClassNotCorrectType(string $source, string $class, string $method, string $expectedErrorMessage)
    {
        $this->expectException(ClassNotFound::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        $this->createReflector($source)->$method($class);
    }

    public function provideReflectClassNotCorrectType()
    {
        return [
            'Class' => [
                '<?php trait Foobar {}',
                'Foobar',
                'reflectClass',
                '"Foobar" is not a class',
            ],
            'Interface' => [
                '<?php class Foobar {}',
                'Foobar',
                'reflectInterface',
                '"Foobar" is not an interface',
            ],
            'Trait' => [
                '<?php interface Foobar {}',
                'Foobar',
                'reflectTrait',
                '"Foobar" is not a trait',
            ]
        ];
    }

    /**
     * @testdox It reflects the value at an offset.
     */
    public function testReflectOffset()
    {
        $source = <<<'EOT'
<?php

$foobar = 'Hello';
$foobar;
EOT
        ;

        $offset = $this->createReflector($source)->reflectOffset($source, 27);
        $this->assertEquals('string', (string) $offset->symbolContext()->type());
        $this->assertEquals('Hello', $offset->frame()->locals()->byName('$foobar')->first()->symbolContext()->value());
    }

    /**
     * @test
     * @dataProvider provideSourceCodeToReflectOn
     */
    public function testReflectOffsetToClosestParent(
        string $source,
        int $offset,
        string $symbolType,
        string $symbolName
    ) {
        $offsetReflection = $this->createReflector($source)
           ->reflectOffsetToClosestParent($source, $offset);
        $symbol = $offsetReflection->symbolContext()->symbol();

        $this->assertEquals($symbolType, $symbol->symbolType());
        $this->assertEquals($symbolName, $symbol->name());
    }

    public function provideSourceCodeToReflectOn()
    {
        $source = <<<'EOT'
<?php

class Foo
{
    private $property;

    public function __construct()
    {

    }
}
EOT;

        return [
            'Cursor inside the class but not on a symbol' => [
                $source,
                43,
                'class',
                'Foo',
            ],
            'Cursor inside a method but not on a symbol' => [
                $source,
                84,
                'method',
                '__construct',
            ],
            'Cursor on a method definition' => [
                $source,
                70,
                'method',
                '__construct',
            ],
        ];
    }

    /**
     * @testdox It reflects the value at an offset.
     */
    public function testReflectOffsetRedeclared()
    {
        $source = <<<'EOT'
<?php

$foobar = 'Hello';
$foobar = 1234;
$foob<>ar;
EOT
        ;

        list($source, $offset) = ExtractOffset::fromSource($source);

        $offset = $this->createReflector($source)->reflectOffset($source, $offset);
        $this->assertEquals('int', (string) $offset->symbolContext()->type());
    }
}

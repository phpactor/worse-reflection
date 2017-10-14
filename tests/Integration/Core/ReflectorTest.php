<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Tests\Integration\Util\CodeHelper;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;

class ReflectorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectClassSuccess
     */
    public function testReflectClassSuccess(string $source, string $class, string $method, string $expectedType)
    {
        $reflection = $this->createReflector($source)->$method($class);
        $this->assertInstanceOf($expectedType, $reflection);
    }

    public function provideReflectClassSuccess()
    {
        return [
            'Class' => [
                '<?php class Foobar {}',
                'Foobar',
                'reflectClass',
                ReflectionClass::class
            ],
            'Interface' => [
                '<?php interface Foobar {}',
                'Foobar',
                'reflectInterface',
                ReflectionInterface::class
            ],
            'Trait' => [
                '<?php trait Foobar {}',
                'Foobar',
                'reflectTrait',
                ReflectionTrait::class
            ]
        ];
    }

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
        $this->assertEquals('string', (string) $offset->symbolInformation()->type());
        $this->assertEquals('Hello', $offset->frame()->locals()->byName('$foobar')->first()->symbolInformation()->value());
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

        list($source, $offset) = CodeHelper::offsetFromCode($source);

        $offset = $this->createReflector($source)->reflectOffset($source, $offset);
        $this->assertEquals('int', (string) $offset->symbolInformation()->type());
    }
}

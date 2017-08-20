<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Reflection\Inference;

use Phpactor\WorseReflection\Core\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\WorseReflection\Core\Reflection\Inference\Variable;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Offset;

class NodeValueResolverTest extends IntegrationTestCase
{
    /**
     * @var ArrayLogger
     */
    private $logger;

    public function setUp()
    {
        $this->logger = new ArrayLogger();
    }

    public function tearDown()
    {
        //var_dump($this->logger->messages());
    }

    /**
     * @dataProvider provideTests
     */
    public function testResolver(string $source, array $locals, int $offset, array $expectedInformation)
    {
        $variables = [];
        foreach ($locals as $name => $type) {
            $variables[] = Variable::fromOffsetNameAndValue(Offset::fromInt(0), $name, SymbolInformation::fromType($type));
        }

        $symbolInfo = $this->resolveNodeAtOffset(LocalAssignments::fromArray($variables), $source, $offset);

        $this->assertExpectedInformation($expectedInformation, $symbolInfo);
    }

    public function provideTests()
    {
        return [
            'It should return none value for whitespace' => [
                '    ', [],
                1,
                ['type' => '<unknown>'],
            ],
            'It should return the name of a class' => [
                <<<'EOT'
<?php

$foo = new ClassName();

EOT
                , [], 23, ['type' => 'ClassName', 'symbol_type' => Symbol::CLASS_]
            ],
            'It should return the fully qualified name of a class' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

$foo = new ClassName();

EOT
                , [], 47, ['type' => 'Foobar\Barfoo\ClassName']
            ],
            'It should return the fully qualified name of a with an imported name.' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use BarBar\ClassName();

$foo = new ClassName();

EOT
                , [], 70, ['type' => 'BarBar\ClassName']
            ],
            'It should return the fully qualified name of a use definition' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use BarBar\ClassName();

$foo = new ClassName();

EOT
                , [], 46, ['type' => 'BarBar\ClassName']
            ],
            'It returns the FQN of a method parameter with a default' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(Barfoo $barfoo = 'test')
    {
    }
}

EOT
                , [], 83, ['type' => 'Foobar\Barfoo\Barfoo', 'symbol_type' => Symbol::VARIABLE]
            ],
            'It returns the type and value of a scalar method parameter' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(string $barfoo = 'test')
    {
    }
}

EOT
                , [], 77, ['type' => 'string', 'value' => 'test']
            ],
            'It returns the value of a method parameter with a constant' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(string $barfoo = 'test')
    {
    }
}

EOT
                , [], 77, ['type' => 'string', 'value' => 'test']
            ],
            'It returns the FQN of a method parameter in an interface' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

interface Foobar
{
    public function hello(World $world);
}

EOT
                , [], 102, ['type' => 'Foobar\Barfoo\World']
            ],
            'It returns the FQN of a method parameter in a trait' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

trait Foobar
{
    public function hello(World $world)
    {
    }
}

EOT
                , [], 94, ['type' => 'Foobar\Barfoo\World']
            ],
            'It returns the value of a method parameter' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(string $barfoo = 'test')
    {
    }
}

EOT
                , [], 77, ['type' => 'string', 'value' => 'test']
            ],
            'It returns the FQN of a static call' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

$foo = Factory::create();

EOT
                , [], 63, ['type' => 'Acme\Factory']
            ],
            'It returns the type of a static call' => [
                <<<'EOT'
<?php

class Factory
{
    public static function create(): string
    {
    }
}

Factory::create();
EOT
                , [], 92, ['type' => 'string']
            ],
            'It returns the FQN of a method parameter' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello(World $world)
    {
    }
}

EOT
                , [], 102, ['type' => 'Foobar\Barfoo\World']
            ],
            'It returns the FQN of variable assigned in frame' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello(World $world)
    {
        echo $world;
    }
}

EOT
                , [ '$world' => Type::fromString('World') ], 127, ['type' => 'World']
            ],
            'It returns type for a call access expression' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Type3
{
    public function foobar(): Foobar
    {
    }
    }

class Type2
{
    public function type3(): Type3
    {
    }
}

class Type1
{
    public function type2(): Type2
    {
    }
}

class Foobar
{
    /**
     * @var Type1
     */
    private $foobar;

    public function hello(Barfoo $world)
    {
        $this->foobar->type2()->type3();
    }
}
EOT
            , [
                '$this' => Type::fromString('Foobar\Barfoo\Foobar'),
            ], 384, ['type' => 'Foobar\Barfoo\Type3'],
            ],
            'It returns type for a property access when class has method of same name' => [
                <<<'EOT'
<?php

class Type1
{
    public function asString(): string
    {
    }
}

class Foobar
{
    /**
     * @var Type1
     */
    private $foobar;

    private function foobar(): Hello
    {
    }

    public function hello()
    {
        $this->foobar->asString();
    }
}
EOT
            , [
                '$this' => Type::fromString('Foobar'),
            ], 263, ['type' => 'string'],
            ],
            'It returns type for a new instantiation' => [
                <<<'EOT'
<?php

new Bar();
EOT
                , [], 9, ['type' => 'Bar'],
            ],
            'It returns type for a new instantiation from a variable' => [
                <<<'EOT'
<?php

new $foobar;
EOT
        , [
                '$foobar' => Type::fromString('Foobar'),
        ], 9, ['type' => 'Foobar'],
            ],
            'It returns type for string literal' => [
                <<<'EOT'
<?php

'bar';
EOT
                , [], 9, ['type' => 'string', 'value' => 'bar']
            ],
            'It returns type for float' => [
                <<<'EOT'
<?php

1.2;
EOT
                , [], 9, ['type' => 'float', 'value' => 1.2],
            ],
            'It returns type for integer' => [
                <<<'EOT'
<?php

12;
EOT
                , [], 9, ['type' => 'int', 'value' => 12],
            ],
            'It returns type for bool true' => [
                <<<'EOT'
<?php

true;
EOT
                , [], 9, ['type' => 'bool', 'value' => true],
            ],
            'It returns type for bool false' => [
                <<<'EOT'
<?php

false;
EOT
                , [], 9, ['type' => 'bool', 'value' => false],
            ],
            'It returns type for bool false' => [
                <<<'EOT'
<?php

null;
EOT
                , [], 9, ['type' => 'null', 'value' => null],
            ],
            'It returns type and value for an array' => [
                <<<'EOT'
<?php

[ 'one' => 'two', 'three' => 3 ];
EOT
                , [], 8, ['type' => 'array', 'value' => [ 'one' => 'two', 'three' => 3]],
            ],
            'It type for a class constant' => [
                <<<'EOT'
<?php

$foo = Foobar::HELLO;

class Foobar
{
    const HELLO = 'string';
}
EOT
                , [], 25, ['type' => 'string'],
            ],
            'Static method access' => [
                <<<'EOT'
<?php

class Foobar
{
    public static function foobar(): Hello {}
}

Foobar::foobar();

class Hello
{
}
EOT
              , [], 86, ['type' => 'Hello'],
          ],
            'Static constant access' => [
                <<<'EOT'
<?php

Foobar::HELLO_CONSTANT;

class Foobar
{
    const HELLO_CONSTANT = 'hello';
}
EOT
                , [], 19, ['type' => 'string'],
            ],
        ];
    }

    /**
     * @dataProvider provideValues
     */
    public function testValues(string $source, array $variables, int $offset, array $expected)
    {
        $information = $this->resolveNodeAtOffset(LocalAssignments::fromArray($variables), $source, $offset);
        $this->assertExpectedInformation($expected, $information);
    }

    public function provideValues()
    {
        return [
            'It returns type for an array access' => [
                <<<'EOT'
<?php

$array['test'];
EOT
                , [
                    Variable::fromOffsetNameAndValue(
                        Offset::fromInt(0),
                        '$array',
                        SymbolInformation::fromTypeAndValue(
                            Type::array(),
                            ['test' => 'tock']
                        )
                    )
                ], 8, ['type' => 'string', 'value' => 'tock']
            ],
            'It returns type for an array assignment' => [
                <<<'EOT'
<?php

$hello = $array['barfoo'];
EOT
                , [
                    Variable::fromOffsetNameAndValue(
                        Offset::fromInt(0),
                        '$array',
                        SymbolInformation::fromTypeAndValue(
                            Type::array(),
                            ['barfoo' => 'tock']
                        )
                    )
                ], 18, ['type' => 'string', 'value' => 'tock']
            ],
            'It returns nested array value' => [
                <<<'EOT'
<?php

$hello = $array['barfoo']['tock'];
EOT
                , [
                    Variable::fromOffsetNameAndValue(
                        Offset::fromInt(0),
                        '$array',
                        SymbolInformation::fromTypeAndValue(
                            Type::array(),
                            ['barfoo' => [ 'tock' => 777 ]]
                        )
                    )
                ], 18, ['type' => 'int', 'value' => 777]
            ],
            'It returns type for self' => [
                <<<'EOT'
<?php

class Foobar
{
    public function foobar(Barfoo $barfoo = 'test')
    {
        self::
    }
}
EOT
                , [], 90, ['type' => 'Foobar']
            ],
            'It returns type for parent' => [
                <<<'EOT'
<?php

class ParentClass {}

class Foobar extends ParentClass
{
    public function foobar(Barfoo $barfoo = 'test')
    {
        parent::
    }
}
EOT
                , [], 134, ['type' => 'ParentClass']
            ],
            'It assumes true for ternary expressions' => [
                <<<'EOT'
<?php

$barfoo ? 'foobar' : 'barfoo';
EOT
                , [], 16, ['type' => 'string', 'value' => 'foobar']
            ],
            'It uses condition value if ternery "if" is empty' => [
                <<<'EOT'
<?php

'string' ?: new \stdClass();
EOT
                , [], 17, ['type' => 'string', 'value' => 'string']
            ],
            'It returns unknown for ternary expressions with unknown condition values' => [
                <<<'EOT'
<?php

$barfoo ?: new \stdClass();
EOT
                , [], 16, ['type' => '<unknown>']
            ],
        ];
    }

    /**
     * These tests test the case where a class in the resolution tree was not found, however
     * their usefulness is limited because we use the StringSourceLocator for these tests which
     * "always" finds the source.
     *
     * @dataProvider provideNotResolvableClass
     */
    public function testNotResolvableClass(string $source, int $offset)
    {
        $value = $this->resolveNodeAtOffset(LocalAssignments::fromArray([
            Variable::fromOffsetNameAndValue(
                Offset::fromInt(0),
                '$this',
                SymbolInformation::fromType(Type::fromString('Foobar'))
            ),
        ]), $source, $offset);
        $this->assertEquals(SymbolInformation::none(), $value);
    }

    public function provideNotResolvableClass()
    {
        return [
            'Calling property method for non-existing class' => [
                <<<'EOT'
<?php

class Foobar
{
    /**
     * @var NonExisting
     */
    private $hello;

    public function hello()
    {
        $this->hello->foobar();
    }
} 
EOT
        , 147
        ],
        'Class extends non-existing class' => [
            <<<'EOT'
<?php

class Foobar extends NonExisting
{
    public function hello()
    {
        $hello = $this->foobar();
    }
}
EOT
        , 126
        ],
        'Method returns non-existing class' => [
            <<<'EOT'
<?php

class Foobar
{
    private function hai(): Hai
    {
    }

    public function hello()
    {
        $this->hai()->foo();
    }
}
EOT
        , 128
        ],
        'Method returns class which extends non-existing class' => [
            <<<'EOT'
<?php

class Foobar
{
    private function hai(): Hai
    {
    }

    public function hello()
    {
        $this->hai()->foo();
    }
}

class Hai extends NonExisting
{
}
EOT
        , 128
        ],
        'Static method returns non-existing class' => [
            <<<'EOT'
<?php

ArrGoo::hai()->foo();

class Foobar
{
    public static function hai(): Foo
    {
    }
}
EOT
        , 27
        ],
    ];
    }

    private function resolveNodeAtOffset(LocalAssignments $assignments, string $source, int $offset)
    {
        $frame = new Frame($assignments);
        $node = $this->parseSource($source)->getDescendantNodeAtPosition($offset);
        $typeResolver = new NodeValueResolver($this->createReflector($source), $this->logger);

        return $typeResolver->resolveNode($frame, $node);
    }

    private function assertExpectedInformation(array $expectedInformation, SymbolInformation $information)
    {
        foreach ($expectedInformation as $name => $value) {
            switch ($name) {
                case 'type':
                    $this->assertEquals($value, (string) $information->type());
                    continue;
                case 'value':
                    $this->assertEquals($value, $information->value());
                    continue;
                case 'symbol_type':
                    $this->assertEquals($value, $information->symbol()->symbolType());
                    continue;
                default:
                    throw new \RuntimeException(sprintf('Do not know how to test symbol information attribute "%s"', $name));
            } 
        }
    }
}

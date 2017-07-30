<?php

namespace Phpactor\WorseReflection\Tests\Integration\Reflection\Inference;

use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\Reflection\Inference\Frame;
use Phpactor\WorseReflection\Reflection\Inference\LocalAssignments;
use Phpactor\WorseReflection\Logger\ArrayLogger;
use Phpactor\WorseReflection\Reflection\Inference\Variable;
use Phpactor\WorseReflection\Reflection\Inference\Value;
use Phpactor\WorseReflection\Offset;

class NodeValueResolverTest extends IntegrationTestCase
{
    private $logger;

    public function setUp()
    {
        $this->logger = new ArrayLogger();
    }

    /**
     * @dataProvider provideTests
     */
    public function testResolver(string $source, array $locals, int $offset, Value $expectedValue)
    {
        $variables = [];
        foreach ($locals as $name => $type) {
            $variables[] = Variable::fromOffsetNameAndValue(Offset::fromInt(0), $name, Value::fromType($type));
        }

        $value = $this->resolveNodeAtOffset(LocalAssignments::fromArray($variables), $source, $offset);

        $this->assertEquals($expectedValue, $value);
    }

    public function provideTests()
    {
        return [
            'It should return none value for whitespace' => [
                '    ', [],
                1,
                Value::none()
            ],
            'It should return the name of a class' => [
                <<<'EOT'
<?php

$foo = new ClassName();

EOT
                , [], 23, Value::fromType(Type::fromString('ClassName'))
            ],
            'It should return the fully qualified name of a class' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

$foo = new ClassName();

EOT
                , [], 47, Value::fromType(Type::fromString('Foobar\Barfoo\ClassName'))
            ],
            'It should return the fully qualified name of a with an imported name.' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use BarBar\ClassName();

$foo = new ClassName();

EOT
                , [], 70, Value::fromType(Type::fromString('BarBar\ClassName'))
            ],
            'It should return the fully qualified name of a use definition' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use BarBar\ClassName();

$foo = new ClassName();

EOT
                , [], 46, Value::fromType(Type::fromString('BarBar\ClassName'))
            ],
            'It returns the FQN of a method parameter' => [
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
                , [], 77, Value::fromType(Type::fromString('Foobar\Barfoo\Barfoo'))
            ],
            'It returns the type of a scalar parameter' => [
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
                , [], 77, Value::fromTypeAndValue(Type::string(), 'test')
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
                , [], 102, Value::fromType(Type::fromString('Foobar\Barfoo\World'))
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
                , [], 94, Value::fromType(Type::fromString('Foobar\Barfoo\World'))
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
                , [], 77, Value::fromTypeAndValue(Type::string(), 'test')
            ],
            'It returns the FQN of a static call' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

$foo = Factory::create();

EOT
                , [], 63, Value::fromType(Type::fromString('Acme\Factory'))
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
                , [], 92, Value::fromType(Type::string())
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
                , [], 102, Value::fromType(Type::fromString('Foobar\Barfoo\World'))
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
                , [ '$world' => Type::fromString('World') ], 127, Value::fromType(Type::fromString('World'))
            ],
            'It returns type for a member access expression' => [
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
            ], 384, Value::fromType(Type::fromString('Foobar\Barfoo\Type3')),
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
            ], 247, Value::fromType(Type::string()),
            ],
            'It returns type for a new instantiation' => [
                <<<'EOT'
<?php

new Bar();
EOT
                , [], 9, Value::fromType(Type::fromString('Bar')),
            ],
            'It returns type for string literal' => [
                <<<'EOT'
<?php

'bar';
EOT
                , [], 9, Value::fromTypeAndValue(Type::string(), 'bar')
            ],
            'It returns type for float' => [
                <<<'EOT'
<?php

1.2;
EOT
                , [], 9, Value::fromTypeAndValue(Type::float(), 1.2),
            ],
            'It returns type for integer' => [
                <<<'EOT'
<?php

12;
EOT
                , [], 9, Value::fromTypeAndValue(Type::int(), 12),
            ],
            'It returns type for bool true' => [
                <<<'EOT'
<?php

true;
EOT
                , [], 9, Value::fromTypeAndValue(Type::bool(), true),
            ],
            'It returns type for bool false' => [
                <<<'EOT'
<?php

false;
EOT
                , [], 9, Value::fromTypeAndValue(Type::bool(), false),
            ],
            'It returns type for bool false' => [
                <<<'EOT'
<?php

null;
EOT
                , [], 9, Value::fromTypeAndValue(Type::null(), null),
            ],
            'It returns type and value for an array' => [
                <<<'EOT'
<?php

[ 'one' => 'two', 'three' => 3 ];
EOT
                , [], 8, Value::fromTypeAndValue(Type::array(), [ 'one' => 'two', 'three' => 3]),
            ],
        ];

    }

    /**
     * @dataProvider provideValues
     */
    public function testValues(string $source, array $variables, int $offset, Value $expectedValue)
    {
        $value = $this->resolveNodeAtOffset(LocalAssignments::fromArray($variables), $source, $offset);
        $this->assertEquals($expectedValue, $value);
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
                        Value::fromTypeAndValue(
                            Type::array(),
                            ['test' => 'tock']
                        )
                    )
                ], 8, Value::fromTypeAndValue(Type::string(), 'tock')
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
                        Value::fromTypeAndValue(
                            Type::array(),
                            ['barfoo' => 'tock']
                        )
                    )
                ], 18, Value::fromTypeAndValue(Type::string(), 'tock')
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
                        Value::fromTypeAndValue(
                            Type::array(),
                            ['barfoo' => [ 'tock' => 777 ]]
                        )
                    )
                ], 18, Value::fromTypeAndValue(Type::int(), 777)
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
}

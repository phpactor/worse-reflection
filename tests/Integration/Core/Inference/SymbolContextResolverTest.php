<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\TestUtils\ExtractOffset;

class SymbolContextResolverTest extends IntegrationTestCase
{
    public function tearDown()
    {
        //var_dump($this->logger);
    }

    /**
     * @dataProvider provideGeneral
     */
    public function testGeneral(string $source, array $locals, array $expectedInformation)
    {
        $variables = [];
        foreach ($locals as $name => $varSymbolInfo) {
            if ($varSymbolInfo instanceof Type) {
                $varSymbolInfo = SymbolContext::for(
                    Symbol::fromTypeNameAndPosition(
                        'variable',
                        $name,
                        Position::fromStartAndEnd(0, 0)
                    )
                )->withType($varSymbolInfo);
            }

            $variables[] = Variable::fromSymbolContext($varSymbolInfo);
        }

        $symbolInfo = $this->resolveNodeAtOffset(LocalAssignments::fromArray($variables), $source);

        $this->assertExpectedInformation($expectedInformation, $symbolInfo);
    }

    /**
     * @dataProvider provideValues
     */
    public function testValues(string $source, array $variables, array $expected)
    {
        $information = $this->resolveNodeAtOffset(LocalAssignments::fromArray($variables), $source);
        $this->assertExpectedInformation($expected, $information);
    }

    /**
     * These tests test the case where a class in the resolution tree was not found, however
     * their usefulness is limited because we use the StringSourceLocator for these tests which
     * "always" finds the source.
     *
     * @dataProvider provideNotResolvableClass
     */
    public function testNotResolvableClass(string $source)
    {
        $value = $this->resolveNodeAtOffset(LocalAssignments::fromArray([
            Variable::fromSymbolContext(
                SymbolContext::for(Symbol::fromTypeNameAndPosition(
                    Symbol::CLASS_,
                    'bar',
                    Position::fromStartAndEnd(0, 0)
                ))->withType(Type::fromString('Foobar'))
            ),
        ]), $source);
        $this->assertEquals(Type::unknown(), $value->type());
    }

    public function provideGeneral()
    {
        return [
            'It should return none value for whitespace' => [
                '  <>  ', [],
                ['type' => '<unknown>'],
            ],
            'It should return the name of a class' => [
                <<<'EOT'
<?php

$foo = new Cl<>assName();

EOT
                , [], ['type' => 'ClassName', 'symbol_type' => Symbol::CLASS_]
            ],
            'It should return the fully qualified name of a class' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

$foo = new Cl<>assName();

EOT
                , [], ['type' => 'Foobar\Barfoo\ClassName']
            ],
            'It should return the fully qualified name of a with an imported name.' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use BarBar\ClassName();

$foo = new Clas<>sName();

EOT
                , [], ['type' => 'BarBar\ClassName', 'symbol_type' => Symbol::CLASS_, 'symbol_name' => 'ClassName']
            ],
            'It should return the fully qualified name of a use definition' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use BarBar\Clas<>sName();

$foo = new ClassName();

EOT
                , [], ['type' => 'BarBar\ClassName']
            ],
            'It returns the FQN of a method parameter with a default' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(Barfoo $<>barfoo = 'test')
    {
    }
}

EOT
                , [], ['type' => 'Foobar\Barfoo\Barfoo', 'symbol_type' => Symbol::VARIABLE, 'symbol_name' => 'barfoo']
            ],
            'It returns the type and value of a scalar method parameter' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(string $b<>arfoo = 'test')
    {
    }
}

EOT
                , [], ['type' => 'string', 'value' => 'test']
            ],
            'It returns the value of a method parameter with a constant' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(string $ba<>rfoo = 'test')
    {
    }
}

EOT
                , [], ['type' => 'string', 'value' => 'test']
            ],
            'It returns the FQN of a method parameter in an interface' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

interface Foobar
{
    public function hello(World $wor<>ld);
}

EOT
                , [], ['type' => 'Foobar\Barfoo\World']
            ],
            'It returns the FQN of a method parameter in a trait' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

trait Foobar
{
    public function hello(<>World $world)
    {
    }
}

EOT
                , [], ['type' => 'Foobar\Barfoo\World', 'symbol_type' => Symbol::CLASS_, 'symbol_name' => 'World']
            ],
            'It returns the value of a method parameter' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(string $<>barfoo = 'test')
    {
    }
}

EOT
                , [], ['type' => 'string', 'value' => 'test']
            ],
            'It returns the FQN of a static call' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

$foo = Fac<>tory::create();

EOT
                , [], ['type' => 'Acme\Factory', 'symbol_type' => Symbol::CLASS_]
            ],
            'It returns the FQN of a method parameter' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello(W<>orld $world)
    {
    }
}

EOT
                , [], ['type' => 'Foobar\Barfoo\World']
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
        echo $w<>orld;
    }
}

EOT
                , [ 'world' => Type::fromString('World') ], ['type' => 'World', 'symbol_type' => Symbol::VARIABLE, 'symbol_name' => 'world']
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
        $this->foobar->type2()->type3(<>);
    }
}
EOT
            , [
                'this' => Type::fromString('Foobar\Barfoo\Foobar'),
            ], [
                'type' => 'Foobar\Barfoo\Type3',
                'symbol_type' => Symbol::METHOD,
                'symbol_name' => 'type3',
                'container_type' => 'Foobar\Barfoo\Type2',
            ],
            ],
            'It returns type for a method which returns an interface type' => [
                <<<'EOT'
<?php

interface Barfoo
{
    public function foo(): string;
}

class Foobar
{
    public function hello(): Barfoo
    {
    }

    public function goodbye()
    {
        $this->hello()->foo(<>);
    }
}
EOT
            , [
                'this' => Type::fromString('Foobar'),
            ], [
                'type' => 'string',
                'symbol_type' => Symbol::METHOD,
                'symbol_name' => 'foo',
                'container_type' => 'Barfoo',
            ],
            ],
            'It returns class type for parent class for parent method' => [
                <<<'EOT'
<?php

class Type3 {}

class Barfoo
{
    public function type3(): Type3
    {
    }
}

class Foobar extends Barfoo
{
    /**
     * @var Type1
     */
    private $foobar;

    public function hello(Barfoo $world)
    {
        $this->type3(<>);
    }
}
EOT
            , [
                'this' => Type::fromString('Foobar'),
            ], [
                'type' => 'Type3',
                'symbol_type' => Symbol::METHOD,
                'symbol_name' => 'type3',
                'container_type' => 'Barfoo',
            ],
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
        $this->foobar->asString(<>);
    }
}
EOT
            , [
                'this' => Type::fromString('Foobar'),
            ], ['type' => 'string'],
            ],
            'It returns type for a new instantiation' => [
                <<<'EOT'
<?php

new <>Bar();
EOT
                , [], ['type' => 'Bar'],
            ],
            'It returns type for a new instantiation from a variable' => [
                <<<'EOT'
<?php

new $<>foobar;
EOT
        , [
                'foobar' => Type::fromString('Foobar'),
        ], ['type' => 'Foobar'],
            ],
            'It returns type for string literal' => [
                <<<'EOT'
<?php

'bar<>';
EOT
                , [], ['type' => 'string', 'value' => 'bar', 'symbol_type' => Symbol::STRING ]
            ],
            'It returns type for float' => [
                <<<'EOT'
<?php

1.<>2;
EOT
                , [], ['type' => 'float', 'value' => 1.2, 'symbol_type' => Symbol::NUMBER],
            ],
            'It returns type for integer' => [
                <<<'EOT'
<?php

12<>;
EOT
                , [], ['type' => 'int', 'value' => 12, 'symbol_type' => Symbol::NUMBER],
            ],
            'It returns type for bool true' => [
                <<<'EOT'
<?php

tr<>ue;
EOT
                , [], ['type' => 'bool', 'value' => true, 'symbol_type' => Symbol::BOOLEAN],
            ],
            'It returns type for bool false' => [
                <<<'EOT'
<?php

<>false;
EOT
                , [], ['type' => 'bool', 'value' => false, 'symbol_type' => Symbol::BOOLEAN],
            ],
            'It returns type null' => [
                <<<'EOT'
<?php

n<>ull;
EOT
                , [], ['type' => 'null', 'value' => null],
            ],
            'It returns type null case insensitive' => [
                <<<'EOT'
<?php

N<>ULL;
EOT
                , [], ['type' => 'null', 'value' => null],
            ],
            'It returns type and value for an array' => [
                <<<'EOT'
<?php

[ 'one' => 'two', 'three' => 3 <>];
EOT
                , [], ['type' => 'array', 'value' => [ 'one' => 'two', 'three' => 3]],
            ],
            'Empty array' => [
                <<<'EOT'
<?php

[  <>];
EOT
                , [], ['type' => 'array', 'value' => [ ]],
            ],
            'It type for a class constant' => [
                <<<'EOT'
<?php

$foo = Foobar::HELL<>O;

class Foobar
{
    const HELLO = 'string';
}
EOT
                , [], ['type' => 'string'],
            ],
            'Static method access' => [
                <<<'EOT'
<?php

class Foobar
{
    public static function foobar(): Hello {}
}

Foobar::fooba<>r();

class Hello
{
}
EOT
              , [], ['type' => 'Hello'],
          ],
            'Static constant access' => [
                <<<'EOT'
<?php

Foobar::HELLO_<>CONSTANT;

class Foobar
{
    const HELLO_CONSTANT = 'hello';
}
EOT
                , [], ['type' => 'string'],
            ],
            'Member access with variable' => [
                <<<'EOT'
<?php

$foobar = new Foobar();
$foobar->$barfoo(<>);

class Foobar
{
}
EOT
                , [], ['type' => '<unknown>'],
            ],
            'Member access with valued variable' => [
                <<<'EOT'
<?php

class Foobar
{
    public function hello(): string {}
}

$foobar->$barfoo(<>);
EOT
                , [
                    'foobar' => Type::fromString('Foobar'),
                    'barfoo' => SymbolContext::for(
                        Symbol::fromTypeNameAndPosition(Symbol::STRING, 'barfoo', Position::fromStartAndEnd(0, 0))
                    )
                    ->withType(Type::string())->withValue('hello')
                ], ['type' => 'string'],
            ],
            'It returns type of property' => [
                <<<'EOT'
<?php

class Foobar
{
    /**
     * @var stdClass
     */
    private $std<>Class;
}
EOT
                , [], ['type' => 'stdClass', 'symbol_name' => 'stdClass'],
            ],
        ];
    }

    public function provideValues()
    {
        return [
            'It returns type for self' => [
                <<<'EOT'
<?php

class Foobar
{
    public function foobar(Barfoo $barfoo = 'test')
    {
        sel<>f::
    }
}
EOT
                , [], ['type' => 'Foobar']
            ],
            'It returns type for static' => [
                <<<'EOT'
<?php

class Foobar
{
    public function foobar(Barfoo $barfoo = 'test')
    {
        stat<>ic::
    }
}
EOT
                , [], ['type' => 'Foobar']
            ],
            'It returns type for parent' => [
                <<<'EOT'
<?php

class ParentClass {}

class Foobar extends ParentClass
{
    public function foobar(Barfoo $barfoo = 'test')
    {
        pare<>nt::
    }
}
EOT
                , [], ['type' => 'ParentClass']
            ],
            'It assumes true for ternary expressions' => [
                <<<'EOT'
<?php

$barfoo ? <>'foobar' : 'barfoo';
EOT
                , [], ['type' => 'string', 'value' => 'foobar']
            ],
            'It uses condition value if ternery "if" is empty' => [
                <<<'EOT'
<?php

'string' ?:<> new \stdClass();
EOT
                , [], ['type' => 'string', 'value' => 'string']
            ],
            'It returns unknown for ternary expressions with unknown condition values' => [
                <<<'EOT'
<?php

$barfoo ?:<> new \stdClass();
EOT
                , [], ['type' => '<unknown>']
            ],

            'It shows the symbol name for a method declartion' => [
                <<<'EOT'
<?php

class Foobar
{
    public function me<>thod()
    {
    }
}
EOT
                , [], [
                    'symbol_type' => Symbol::METHOD,
                    'symbol_name' => 'method',
                    'container_type' => 'Foobar',
                ]
            ],
            'Class name' => [
                <<<'EOT'
<?php

class Fo<>obar
{
}
EOT
                , [], ['type' => 'Foobar', 'symbol_type' => Symbol::CLASS_, 'symbol_name' => 'Foobar'],
            ],
            'Property name' => [
                <<<'EOT'
<?php

class Foobar
{
    private $a<>aa = 'asd';
}
EOT
                , [], ['type' => '<unknown>', 'symbol_type' => Symbol::PROPERTY, 'symbol_name' => 'aaa', 'container_type' => 'Foobar'],
            ],
            'Constant name' => [
                <<<'EOT'
<?php

class Foobar
{
    const AA<>A = 'aaa';
}
EOT
                , [], [
                    'type' => '<unknown>',
                    'symbol_type' => Symbol::CONSTANT,
                    'symbol_name' => 'AAA',
                    'container_type' => 'Foobar'
                ],
            ],
            'Function name' => [
                <<<'EOT'
<?php

function_<>call();
EOT
                , [], ['type' => '<unknown>'],
            ],
            'Trait name' => [
                <<<'EOT'
<?php

trait Bar<>bar
{
}
EOT
                , [], ['symbol_type' => 'class', 'symbol_name' => 'Barbar', 'type' => 'Barbar' ],
            ],
        ];
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
        $this->hello->foobar(<>);
    }
} 
EOT
        ],
        'Class extends non-existing class' => [
            <<<'EOT'
<?php

class Foobar extends NonExisting
{
    public function hello()
    {
        $hello = $this->foobar(<>);
    }
}
EOT
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
        $this->hai()->foo(<>);
    }
}
EOT
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
        $this->hai()->foo(<>);
    }
}

class Hai extends NonExisting
{
}
EOT
        ],

        'Static method returns non-existing class' => [
            <<<'EOT'
<?php

ArrGoo::hai()->foo(<>);

class Foobar
{
    public static function hai(): Foo
    {
    }
}
EOT
        ],
    ];
    }

    public function testAttachesScope()
    {
        $source = <<<'EOT'
<?php

namespace Hello;

use Goodbye;
use Adios;

new Foob<>o;
EOT
        ;
        $context = $this->resolveNodeAtOffset(LocalAssignments::create(), $source);
        $this->assertCount(2, $context->scope()->nameImports());
    }

    private function resolveNodeAtOffset(LocalAssignments $assignments, string $source): SymbolContext
    {
        $frame = new Frame('test', $assignments);

        list($source, $offset) = ExtractOffset::fromSource($source);
        $node = $this->parseSource($source)->getDescendantNodeAtPosition($offset);

        $resolver = new SymbolContextResolver($this->createReflector($source), $this->logger());

        return $resolver->resolveNode($frame, $node);
    }

    private function assertExpectedInformation(array $expectedInformation, SymbolContext $information)
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
                case 'symbol_name':
                    $this->assertEquals($value, $information->symbol()->name());
                    continue;
                case 'container_type':
                    $this->assertEquals($value, (string) $information->containerType());
                    continue;
                case 'log':
                    $this->assertContains($value, implode(' ', $this->logger->messages()));
                    continue;
                default:
                    throw new \RuntimeException(sprintf('Do not know how to test symbol information attribute "%s"', $name));
            }
        }
    }
}

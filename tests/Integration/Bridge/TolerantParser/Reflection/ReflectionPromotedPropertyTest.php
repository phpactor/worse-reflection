<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class ReflectionPromotedPropertyTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideConsturctorPropertyPromotion
     */
    public function testReflectProperty(string $source, string $class, \Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class->properties());
    }

    public function provideConsturctorPropertyPromotion(): \Generator
    {
        yield 'Typed properties' => [
                <<<'EOT'
<?php

namespace Test;

class Barfoo
{
    public function __construct(
        private string $foobar
        private int $barfoo,
        private string|int $baz
    ) {}
}
EOT
                ,
                'Test\Barfoo',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertTrue($properties->get('foobar')->isPromoted());
                    $this->assertEquals(
                        Type::string(),
                        $properties->get('foobar')->type()
                    );
                    $this->assertEquals(
                        Type::int(),
                        $properties->get('barfoo')->type()
                    );
                    $this->assertEquals(
                        Types::fromTypes([
                            Type::string(),
                            Type::int(),
                        ]),
                        $properties->get('baz')->inferredTypes()
                    );
                },
            ];

        yield 'Nullable' => [
                '<?php class Barfoo { public function __construct(private ?string $foobar){}}',
                'Barfoo',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(
                        Type::string()->asNullable(),
                        $properties->get('foobar')->type()
                    );
                },
            ];

        yield 'No types' => [
                '<?php class Barfoo { public function __construct(private $foobar){}}',
                'Barfoo',
                function (ReflectionPropertyCollection $properties) {
                    $this->assertEquals(
                        Type::undefined(),
                        $properties->get('foobar')->type()
                    );
                },
            ];
    }
}

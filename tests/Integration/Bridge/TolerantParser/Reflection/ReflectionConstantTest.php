<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Visibility;

class ReflectionConstantTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionConstant
     */
    public function testReflectConstant(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        assert($class instanceof ReflectionClass);
        $assertion($class->constants());
    }

    public function provideReflectionConstant()
    {
        yield 'Returns declaring class' => [
            <<<'EOT'
<?php

class Foobar
{
    const FOOBAR = 'foobar';
}
EOT
            ,
            'Foobar',
            function (ReflectionConstantCollection $constants) {
                $this->assertEquals('Foobar', $constants->get('FOOBAR')->declaringClass()->name()->__toString());
                $this->assertEquals(Visibility::public(), $constants->first()->visibility());
            }
        ];

        yield 'Returns visibility' => [
            <<<'EOT'
<?php

class Foobar
{
    private const FOOBAR = 'foobar';
}
EOT
            ,
            'Foobar',
            function (ReflectionConstantCollection $constants) {
                $this->assertEquals(Visibility::private(), $constants->first()->visibility());
            }
        ];

        yield 'Returns docblock' => [
            <<<'EOT'
<?php

class Foobar
{
    /** Hello! */
    private const FOOBAR = 'foobar';
}
EOT
            ,
            'Foobar',
            function (ReflectionConstantCollection $constants) {
                $this->assertContains('/** Hello! */', $constants->first()->docblock()->raw());
            }
        ];

        yield 'Returns type' => [
            <<<'EOT'
<?php

class Foobar
{
    const FOOBAR = 'foobar';
}
EOT
            ,
            'Foobar',
            function (ReflectionConstantCollection $constants) {
                $this->assertEquals('string', $constants->first()->type()->short());
            }
        ];

        yield 'Doesnt return inferred retutrn types (not implemented)' => [
            <<<'EOT'
<?php

class Foobar
{
    /** @var int */
    const FOOBAR = 'foobar';
}
EOT
            ,
            'Foobar',
            function (ReflectionConstantCollection $constants) {
                $this->assertEquals('string', $constants->first()->inferredTypes()->best()->short());
            }
        ];
    }
}

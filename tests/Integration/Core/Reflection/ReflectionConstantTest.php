<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;

class ReflectionConstantTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionConstant
     */
    public function testReflectConstant(string $source, string $class, \Closure $assertion)
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class->constants());
    }

    public function provideReflectionConstant()
    {
        return [
            'Returns declaring class' => [
                <<<'EOT'
<?php

class Foobar
{
    const FOOBAR = 'foobar';
}
EOT
                ,
                'Foobar',
                function ($constants) {
                    $this->assertEquals('Foobar', $constants->get('FOOBAR')->declaringClass()->name()->__toString());
                },
            ],
        ];
    }
}

<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Tests\Integration\Util\CodeHelper;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionArgumentCollection;

class ReflectionArgumentTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionMethod
     */
    public function testReflectMethodCall(string $source, array $frame, \Closure $assertion)
    {
        list($source, $offset) = CodeHelper::offsetFromCode($source);
        $reflection = $this->createReflector($source)->reflectNode($source, $offset);
        $assertion($reflection->arguments());
    }

    public function provideReflectionMethod()
    {
        return [
            'It guesses the name from the var name' => [
                <<<'EOT'
<?php

$foo->b<>ar($foo);
EOT
                , [
                ],
                function (ReflectionArgumentCollection $arguments) {
                    $this->assertEquals('foo', $arguments->first()->guessName());
                },
            ],
            'It guesses the name from return type' => [
                <<<'EOT'
<?php

class AAA
{
    public function bob(): Alice
    {
    }
}

$foo = new AAA();
$foo->b<>ar($foo->bob());
EOT
                , [
                ],
                function (ReflectionArgumentCollection $arguments) {
                    $this->assertEquals('alice', $arguments->first()->guessName());
                },
            ],
            'It returns a generated name if it cannot be determined' => [
                <<<'EOT'
<?php

class AAA
{
}

$foo = new AAA();
$foo->b<>ar($foo->bob(), $foo->zed());
EOT
                , [
                ],
                function (ReflectionArgumentCollection $arguments) {
                    $this->assertEquals('argument0', $arguments->first()->guessName());
                    $this->assertEquals('argument1', $arguments->last()->guessName());
                },
            ],
            'It returns the argument type' => [
                <<<'EOT'
<?php

$integer = 1;
$foo->b<>ar($integer);
EOT
                , [
                ],
                function (ReflectionArgumentCollection $arguments) {
                    $this->assertEquals('int', (string) $arguments->first()->type());
                },
            ],
            'It returns the value' => [
                <<<'EOT'
<?php

$integer = 1;
$foo->b<>ar($integer);
EOT
                , [
                ],
                function (ReflectionArgumentCollection $arguments) {
                    $this->assertEquals(1, $arguments->first()->value());
                },
            ],
            'It returns the position' => [
                <<<'EOT'
<?php

$foo->b<>ar($integer);
EOT
                , [
                ],
                function (ReflectionArgumentCollection $arguments) {
                    $this->assertEquals(17, $arguments->first()->position()->start());
                    $this->assertEquals(25, $arguments->first()->position()->end());
                },
            ],
        ];
    }
}

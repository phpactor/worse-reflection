<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflector;

use Closure;
use Generator;
use Phpactor\WorseReflection\Core\Inference\Problem;
use Phpactor\WorseReflection\Core\Inference\Problems;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class TolerantSourceCodeReflectorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideAnalyze
     */
    public function testAnalyze(string $sourceCode, Closure $assertion): void
    {
        $reflector = $this->createReflector($sourceCode);
        $diagnostics = $reflector->analyze($sourceCode);
        $assertion($diagnostics);
    }

    public function provideAnalyze(): Generator
    {
        yield [
            '<?php $foo = new Foo(); $foo = $foo->foo();',
            function (Problems $problems) {
                self::assertCount(1, $problems);
                self::assertEquals(Problem::CLASS_NOT_FOUND, $problems->first()->code());
            }
        ];

        yield [
            '<?php class Foo{}; $foo = new Foo(); $foo = $foo->foo();',
            function (Problems $problems) {
                self::assertCount(1, $problems);
                self::assertEquals(Problem::METHOD_NOT_FOUND, $problems->first()->code());
            }
        ];
    }
}

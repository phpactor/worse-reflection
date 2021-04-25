<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflector;

use Generator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class TolerantSourceCodeReflectorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideAnalyze
     */
    public function testAnalyze(string $sourceCode, int $diagnosticCount): void
    {
        $reflector = ReflectorBuilder::create()->build();
        $diagnostics = $reflector->analyze($sourceCode);
        self::assertCount($diagnosticCount, $diagnostics);
    }

    public function provideAnalyze(): Generator
    {
        yield [
            '<?php $foo = $a->foo();',
            1
        ];
    }
}

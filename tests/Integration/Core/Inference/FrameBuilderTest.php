<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\TestUtils\ExtractOffset;

class FrameBuilderTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideForMethod
     */
    public function testForMethod(string $source, array $classAndMethod, \Closure $assertion)
    {
        list($className, $methodName) = $classAndMethod;
        $reflector = $this->createReflector($source);
        $method = $reflector->reflectClassLike(ClassName::fromString($className))->methods()->get($methodName);
        $frame = $method->frame();

        $assertion($frame, $this->logger());
    }

    public function provideForMethod()
    {
        yield 'Tolerates missing tokens' => [
            <<<'EOT'
<?php

class Foobar
{
    public function hello()
    {
        $reflection = )>classReflector->reflect(TestCase::class);
    }
}
EOT
        , [ 'Foobar', 'hello' ], function (Frame $frame, $logger) {
            $this->assertContains('Non-node class passed to resolveNode, got', (string) $frame->problems());
        }];
    }
}

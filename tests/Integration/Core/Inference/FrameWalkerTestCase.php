<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Closure;
use Generator;

abstract class FrameWalkerTestCase extends IntegrationTestCase
{
    /**
     * @dataProvider provideWalk
     */
    public function testWalk(string $source, Closure $assertion)
    {
        list($source, $offset) = ExtractOffset::fromSource($source);
        $reflector = $this->createReflector($source);
        $offset = $reflector->reflectOffset($source, $offset);
        $assertion($offset->frame());
    }

    abstract public function provideWalk(): Generator;

}

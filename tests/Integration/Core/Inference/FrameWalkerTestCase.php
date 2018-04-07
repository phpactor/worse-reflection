<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Closure;

class FrameWalkerTestCase extends IntegrationTestCase
{
    /**
     * @dataProvider provideWalks
     */
    public function testWalks(string $source, Closure $assertion)
    {
        list($source, $offset) = ExtractOffset::fromSource($source);
        $reflector = $this->createReflector($source);
        $offset = $reflector->reflectOffset($source, $offset);
        $assertion($offset->frame());
    }

}

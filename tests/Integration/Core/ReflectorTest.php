<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\SourceCode;

class ReflectorTest extends IntegrationTestCase
{
    /**
     * @testdox It reflects the value at an offset.
     */
    public function testReflectOffset()
    {
        $source = <<<'EOT'
<?php

$foobar = 'Hello';
$foobar;
EOT
        ;

        $offset = $this->createReflector($source)->reflectOffset(SourceCode::fromString($source), Offset::fromInt(27));
        $this->assertEquals('string', (string) $offset->value()->type());
        $this->assertEquals('Hello', $offset->frame()->locals()->byName('$foobar')->first()->value()->value());
    }
}

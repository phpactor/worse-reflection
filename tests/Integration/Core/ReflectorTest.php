<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core;

use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Tests\Integration\Util\CodeHelper;

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
        $this->assertEquals('string', (string) $offset->symbolInformation()->type());
        $this->assertEquals('Hello', $offset->frame()->locals()->byName('$foobar')->first()->symbolInformation()->value());
    }

    /**
     * @testdox It reflects the value at an offset.
     */
    public function testReflectOffsetRedeclared()
    {
        $source = <<<'EOT'
<?php

$foobar = 'Hello';
$foobar = 1234;
$foob<>ar;
EOT
        ;

        list($source, $offset) = CodeHelper::offsetFromCode($source);

        $offset = $this->createReflector($source)->reflectOffset(SourceCode::fromString($source), Offset::fromInt($offset));
        $this->assertEquals('int', (string) $offset->symbolInformation()->type());
    }
}

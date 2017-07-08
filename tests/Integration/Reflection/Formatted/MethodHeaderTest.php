<?php

namespace Phpactor\WorseReflection\Tests\Integration\Reflection\Formatted;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\ClassName;

class MethodHeaderTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideMethodHeader
     */
    public function testMethodHeader(string $source, string $class, string $method, $expectedMethodHeader)
    {
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString($class));
        $this->assertEquals($expectedMethodHeader, (string) $class->methods()->get($method)->header());
    }

    public function provideMethodHeader()
    {
        return [
            'It reflects an interface method' => [
                <<<'EOT'
<?php

interface Foobar
{
    public function method();
}
EOT
                ,
                'Foobar', 'method',
                'public function method()',
            ],
            'It reflects an abstract method' => [
                <<<'EOT'
<?php

abstract class Foobar
{
    abstract public function method();
}
EOT
                ,
                'Foobar', 'method',
                'abstract public function method()',
            ],
            'It ignores documentation' => [
                <<<'EOT'
<?php

abstract class Foobar
{
    /**
     * Test
     */
    public function method();
}
EOT
                ,
                'Foobar', 'method',
                'public function method()',
            ],
            'It does not include the method body' => [
                <<<'EOT'
<?php

class Foobar
{
    public function method(): Foobar
    {
    }
}
EOT
                ,
                'Foobar', 'method',
                'public function method(): Foobar',
            ],
        ];
    }
}

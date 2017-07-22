<?php

namespace Phpactor\WorseReflection\Tests\Integration\Reflection\Inference;

use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Reflection\Inference\NodeTypeResolver;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Type;

class FrameBuilderTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideTests
     */
    public function testFrameBuilder(string $source, int $offset, Type $expectedType)
    {
        $this->markTestSkipped('TODO');
        $parser = new Parser();
        $node = $parser->parseSourceFile($source);
        $node = $node->getDescendantNodeAtPosition($offset);

        $typeResolver = new NodeTypeResolver($this->createReflector($source));
        $type = $typeResolver->resolveNode($node);

        $this->assertEquals($expectedType, $type);
    }

    public function provideTests()
    {
        return [
            'It returns the FQN of variable assigned in frame' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello(World $world)
    {
        echo $world;
    }
}

EOT
                , 127, Type::fromString('Foobar\Barfoo\World')
            ],
            'It returns types for reassigned variables' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello(World $world)
    {
        $foobar = $world;
        $foobar;
    }
}

EOT
                , 154, Type::fromString('Foobar\Barfoo\World')
            ],
            'It returns type for $this' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;

class Foobar
{
    public function hello(World $world)
    {
        $this;
    }
}

EOT
                , 126, Type::fromString('Foobar\Barfoo\Foobar')
            ],
            'It returns type for a property' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

use Acme\Factory;
use Things\Response;

class Foobar
{
    /**
     * @var \Hello\World
     */
    private $foobar;

    public function hello(Barfoo $world)
    {
        $this->foobar;
    }
}
EOT
                , 215, Type::fromString('Hello\World')
            ],
            'It returns type for a variable assigned to an access expression' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Type1
{
    public function type2(): Type2
    {
    }
}

class Foobar
{
    /**
     * @var Type1
     */
    private $foobar;

    public function hello(Barfoo $world)
    {
        $foobar = $this->foobar->type2();
        $foobar;
    }
}
EOT
                , 269, Type::fromString('Foobar\Barfoo\Type2')
            ],
            'It returns the FQN for self' => [
                <<<'EOT'
<?php

namespace Foobar\Barfoo;

class Foobar
{
    public function foobar(Barfoo $barfoo)
    {
        self::foobar();
    }
}

EOT
                , 106, Type::fromString('Foobar\Barfoo\Foobar')
            ],
            'It returns type for a for each member (with a docblock)' => [
                <<<'EOT'
<?php

/** @var $foobar Foobar */
foreach ($collection as $foobar) {
    $foobar->foobar();
}
EOT
                , 75, Type::fromString('Foobar')
            ],
        ];

    }
}

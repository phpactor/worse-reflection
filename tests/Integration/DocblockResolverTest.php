<?php

namespace Phpactor\WorseReflection\Tests\Integration;

use Phpactor\WorseReflection\Core\DocblockResolver;
use Phpactor\WorseReflection\Core\Type;

class DocblockResolverTest extends IntegrationTestCase
{
    /**
     * @var DocblockResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->resolver = new DocblockResolver();
    }

    /**
     * @dataProvider provideResolveFromNode
     */
    public function testResolveFromNode(string $source, int $offset, Type $expectedType)
    {
        $node = $this->parseSource($source)->getDescendantNodeAtPosition($offset);
        $type = $this->resolver->nodeType($node);

        $this->assertEquals($expectedType, $type);
    }

    public function provideResolveFromNode()
    {
        return [
            'Returns unknown for no type' => [
                <<<'EOT'
<?php
EOT
                , 4, Type::unknown()
            ],
            'Returns property type' => [
                <<<'EOT'
<?php

/**
 * @var Foobar
 */
$foobar;
EOT
                , 36, Type::fromString('Foobar')
            ],
            'Returns property type with namespace' => [
                <<<'EOT'
<?php

namespace Hello;

class Foo
{
    /**
     * @var Foobar
     */
    private $foobar;
}
EOT
                , 77, Type::fromString('Hello\Foobar')
            ],
            'Returns used type' => [
                <<<'EOT'
<?php

namespace Hello;

use Acme\Bar\Foobar;

/** @var Foobar */
$foobar;
EOT
                , 69, Type::fromString('Acme\Bar\Foobar')
            ],
            'Returns an aliased type' => [
                <<<'EOT'
<?php

namespace Hello;

use Acme\Bar\Barfoo as Foobar;

/** @var Foobar */
$foobar;
}
EOT
                , 77, Type::fromString('Acme\Bar\Barfoo')
            ],
            'Returns a scalar type in a namespace' => [
                <<<'EOT'
<?php

namespace Hello;

/** @var string */
$foobar;
EOT
                , 45, Type::string()
            ],
        ];
    }
}

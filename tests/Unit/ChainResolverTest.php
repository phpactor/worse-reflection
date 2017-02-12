<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\Tests\IntegrationTestCase;
use DTL\WorseReflection\Type;
use DTL\WorseReflection\ClassName;

class ChainResolverTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideResolve
     */
    public function testResolve(string $source, Type $expectedType)
    {
        list($offset, $source) = $this->getOffsetAndSource($source);

        $reflector = $this->getReflectorForSource($source);
        $offset = $reflector->reflectOffsetInSource($offset, $source);
    }

    public function provideResolve()
    {
        return [
            [
                <<<'EOT'
class Class1
{
}

class Class2
{
    public function getClass1(): Class1
    {
    }

    public function execute()
    {
        $this->getClass1();_
    }
}
EOT
                , Type::class(ClassName::fromString('Class1'))
            ],
        ];
    }
}

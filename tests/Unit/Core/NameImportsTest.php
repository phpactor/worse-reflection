<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;
use RuntimeException;

class NameImportsTest extends TestCase
{
    public function testByAlias()
    {
        $imports = NameImports::fromNames([
            'Barfoo' => Name::fromString('Foobar\\Barfoo'),
        ]);

        $this->assertTrue($imports->hasAlias('Barfoo'));
        $this->assertEquals(
            Name::fromString('Foobar\\Barfoo'),
            $imports->getByAlias('Barfoo')
        );
    }

    public function testAliasNotFound()
    {
        $this->expectException(RuntimeException::class);

        $imports = NameImports::fromNames([]);

        $imports->getByAlias('Barfoo');
    }
}

<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection\TraitImport;

use Closure;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitAlias;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImports;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;

class TraitImportsTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideTraitImports
     */
    public function testTraitImports(string $source, Closure $assertion)
    {
        $rootNode = $this->parseSource($source);
        $classDeclaration = $rootNode->getFirstDescendantNode(ClassDeclaration::class);
        $assertion(new TraitImports($classDeclaration));
    }

    public function provideTraitImports()
    {
        yield 'simple use' => [
            '<?php trait A {}; class B { use A; }',
            function (TraitImports $traitImports) {
                $this->assertCount(1, $traitImports);
                $this->assertTrue($traitImports->has('A'));
                $this->assertEquals('A', $traitImports->get('A')->name());
            }
        ];

        yield 'simple use with alias' => [
            '<?php trait A { function foo() {}}; class B { use A { foo as bar; }',
            function (TraitImports $traitImports) {
                $this->assertCount(1, $traitImports);
                $traitImport = $traitImports->get('A');
                $this->assertTrue($traitImport->hasTraitAliases());
                $traitAlias = $traitImport->traitAliases()['foo'];
                assert($traitAlias instanceof TraitAlias);
                $this->assertEquals('foo', $traitAlias->originalName());
                $this->assertEquals('bar', $traitAlias->newName());
            }
        ];

        yield 'simple use with alias and visiblity' => [
            '<?php trait A { function foo() {}}; class B { use A { foo as private bar; bar as protected bar; zed as public aar;}',
            function (TraitImports $traitImports) {
                $traitImport = $traitImports->get('A');
                ;
                assert($traitAlias instanceof TraitAlias);

                $this->assertEquals(Visibility::private(), $traitImport->traitAliases()['foo']->visiblity());
                $this->assertEquals(Visibility::protected(), $traitImport->traitAliases()['bar']->visiblity());
                $this->assertEquals(Visibility::public(), $traitImport->traitAliases()['zed']->visiblity());
            }
        ];

        yield 'does not support insteadof' => [
            '<?php trait A { function foo(){} function bar()}}' .
            'trait B { function foo() {}} ' .
            'class B { use A, B { B::foo insteadof A } }',
            function (TraitImports $traitImports) {
                $traitImport = $traitImports->get('A');
                assert($traitAlias instanceof TraitAlias);
                $this->assertCount(0, $traitImport->traitAliases());
            }
        ];
    }
}

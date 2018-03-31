<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpstan\DocblockParser;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\Phpstan\DocblockParser\PhpstanDocBlockFactory;
use Closure;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;

class PhpstanDocblockParserFactoryTest extends TestCase
{
    /**
     * @dataProvider provideParseDocblock
     */
    public function testParseDocblock(string $docblock, Closure $assetion)
    {
        $factory = new PhpstanDocBlockFactory();
        $docblock = $factory->create($docblock);
        $assetion($docblock);
    }

    public function provideParseDocblock()
    {
        yield 'return type' => [
            '/** @return Foobar */',
            function (DocBlock $block) {
                $this->assertEquals(['Foobar'], $block->returnTypes());
            }
        ];

        yield 'var type' => [
            '/** @var Foobar $foobar */',
            function (DocBlock $block) {
                $vars = $block->vars();
                $this->assertEquals(new DocBlockVars([
                    DocBlockVar::fromVarNameAndType('$foobar', 'Foobar')
                ]), $block->vars());
            }
        ];

        yield 'method type' => [
            '/** @method Foobar foobar() */',
            function (DocBlock $block) {
                $vars = $block->methodTypes('foobar');
                $this->assertEquals(['Foobar'], $block->methodTypes('foobar'));
            }
        ];
    }
}

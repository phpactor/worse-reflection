<?php

namespace Phpactor\WorseReflection\Tests\Unit\DocblockParser\Benchmark;

use Phpactor\WorseReflection\DocblockParser\Lexer;
use Phpactor\WorseReflection\DocblockParser\Parser;
use Phpactor\WorseReflection\Tests\Unit\DocblockParser\Benchmark\AbstractParserBenchCase;

class PhpactorParserBench extends AbstractParserBenchCase
{
    
    private PhpDocParser $parser;

    
    private Lexer $lexer;

    public function setUp(): void
    {
        $this->parser = new Parser();
        $this->lexer = new Lexer();
    }

    public function parse(string $doc): void
    {
        $this->parser->parse($this->lexer->lex($doc));
    }
}

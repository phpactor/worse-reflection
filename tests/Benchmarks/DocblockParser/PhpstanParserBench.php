<?php

namespace Phpactor\WorseReflection\Tests\Unit\DocblockParser\Benchmark;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Phpactor\WorseReflection\Tests\Unit\DocblockParser\Benchmark\AbstractParserBenchCase;

class PhpstanParserBench extends AbstractParserBenchCase
{
    
    private PhpDocParser $parser;

    
    private Lexer $lexer;

    public function setUp(): void
    {
        $this->parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        $this->lexer = new Lexer();
    }

    public function parse(string $doc): void
    {
        $tokens = new TokenIterator($this->lexer->tokenize($doc));
        $this->parser->parse($tokens);
    }
}

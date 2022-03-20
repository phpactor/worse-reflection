<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Parser;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Reflector;

class DocblockParserFactory implements DocBlockFactory
{
    private Lexer $lexer;
    private Parser $parser;
    private Reflector $reflector;

    public function __construct(Reflector $reflector, ?Lexer $lexer = null, ?Parser $parser = null)
    {
        $this->lexer = $lexer ?: new Lexer();
        $this->parser = $parser ?: new Parser();
        $this->reflector = $reflector;
    }

    public function create(string $docblock): DocBlock
    {
        return new ParsedDocblock(
            $this->parser->parse($this->lexer->lex($docblock)),
            new TypeConverter($this->reflector)
        );
    }
}

<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Parser;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\DocBlock\EmptyDocblock;
use Phpactor\WorseReflection\Reflector;

class DocblockParserFactory implements DocBlockFactory
{
    const SUPPORTED_TAGS = [
        '@property',
        '@var',
        '@param',
        '@return',
        '@method',
    ];

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
        if (empty(trim($docblock))) {
            return new EmptyDocblock();
        }

        if (0 === preg_match(sprintf('{(%s)}', implode('|', self::SUPPORTED_TAGS)), $docblock, $matches)) {
            return new EmptyDocblock();
        }

        return new ParsedDocblock(
            $this->parser->parse($this->lexer->lex($docblock)),
            new TypeConverter($this->reflector)
        );
    }
}

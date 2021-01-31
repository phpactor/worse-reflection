<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\Docblock\Lexer;
use Phpactor\Docblock\Parser;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory as CoreDocblockPhpactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock as CoreDocblock;
use Phpactor\Docblock\DocblockFactory as PhpactorDocblockFactory;

class DocblockFactory implements CoreDocblockPhpactory
{
    /**
     * @var Lexer
     */
    private $lexer;
    /**
     * @var Parser
     */
    private $parser;

    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->parser = new Parser();
    }

    public function create(string $docblock): CoreDocblock
    {
        static $cache = [];
        if (isset($cache[$docblock])) {
            return $cache[$docblock];
        }
        $ret = new Docblock($docblock, $this->parser->parse($this->lexer->lex($docblock)));
        $cache[$docblock] = $ret;
        return $ret;
    }
}

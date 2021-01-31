<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\Docblock\Lexer;
use Phpactor\Docblock\Parser;
use Phpactor\WorseReflection\Core\Cache;
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

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->lexer = new Lexer();
        $this->parser = new Parser();
        $this->cache = $cache;
    }

    public function create(string $docblock): CoreDocblock
    {
        return $this->cache->getOrSet($docblock, function () use ($docblock) {
            return new Docblock($docblock, $this->parser->parse($this->lexer->lex($docblock)));
        });
    }
}

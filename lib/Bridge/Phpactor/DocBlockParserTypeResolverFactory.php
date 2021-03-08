<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Parser;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeResolver;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeResolverFactory;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use RuntimeException;

class DocBlockParserTypeResolverFactory implements DocBlockTypeResolverFactory
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(?Lexer $lexer = null, ?Parser $parser = null)
    {
        $this->lexer = $lexer ?: new Lexer();
        $this->parser = $parser ?: new Parser();
    }

    public function create(ReflectionNode $scope, string $docblock): DocBlockTypeResolver
    {
        return new DocBlockParserTypeResolver($scope, $this->parseDocblock($docblock));
    }

    private function parseDocblock(string $docblock): Docblock
    {
        $node = $this->parser->parse($this->lexer->lex($docblock));
        if (!$node instanceof Docblock) {
            throw new RuntimeException('Nope');
        }
        return $node;
    }
}

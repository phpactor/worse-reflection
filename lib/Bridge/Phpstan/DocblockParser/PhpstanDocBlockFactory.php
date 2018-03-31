<?php

namespace Phpactor\WorseReflection\Bridge\Phpstan\DocblockParser;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use PHPStan\PhpDocParser\Parser\TokenIterator;

class PhpstanDocBlockFactory implements DocBlockFactory
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var PhpDocParser
     */
    private $phpDocParser;

    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->phpDocParser = new PhpDocParser(new TypeParser(), new ConstExprParser());
    }

    public function create(string $docblock): DocBlock
    {
        $docblock = trim($docblock);
        if (empty(trim($docblock))) {
            $docblock = '/** */';
        }

        return new PhpstanDocBlock($docblock, $this->phpDocParser, $this->lexer);
    }
}

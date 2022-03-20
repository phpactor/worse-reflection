<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;
use Phpactor\WorseReflection\DocblockParser\Ast\Node;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;


class VariableNode extends Node
{
    protected const CHILD_NAMES = [
        'name'
    ];

    
    public Token $name;

    public function __construct(Token $name)
    {
        $this->name = $name;
    }

    public function name(): Token
    {
        return $this->name;
    }
}

<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;
use Phpactor\WorseReflection\DocblockParser\Ast\TagNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;


class UnknownTag extends TagNode
{
    public Token $name;

    public function __construct(Token $name)
    {
        $this->name = $name;
    }
}

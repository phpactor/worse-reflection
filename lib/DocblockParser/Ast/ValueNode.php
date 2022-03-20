<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;
use Phpactor\WorseReflection\DocblockParser\Ast\Node;


abstract class ValueNode extends Node
{
    /**
     * @return mixed
     */
    abstract public function value();
}

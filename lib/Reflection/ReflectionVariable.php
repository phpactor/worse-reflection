<?php

namespace DTL\WorseReflection\Reflection;

use PhpParser\Node;

class ReflectionVariable
{
    private $name;
    private $node;

    public function __construct(string $name, Node $node)
    {
        $this->name = $name;
        $this->node = $node;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType(): Type
    {
        return Type::fromParserNode($this->node);
    }
}

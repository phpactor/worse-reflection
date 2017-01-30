<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Param;
use DTL\WorseReflection\Type;
use PhpParser\Node\Name;
use DTL\WorseReflection\ClassName;

class ReflectionParameter
{
    private $reflector;
    private $sourceContext;
    private $node;

    public function __construct(
        Reflector $reflector,
        SourceContext $sourceContext,
        Param $node
    )
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->node = $node;
    }

    public function getName() 
    {
        return (string) $this->node->name;
    }

    public function getType(): Type
    {
        if ($this->node->type === 'string') {
            return Type::string();
        }

        if ($this->node->type === 'int') {
            return Type::int();
        }

        if ($this->node->type === 'float') {
            return Type::float();
        }

        if ($this->node->type instanceof Name) {
            return Type::class(ClassName::fromParts($this->node->type->parts));
        }

        return Type::unknown();
    }
    
    public function getDefault()
    {
        if (!$this->node->default) {
            return null;
        }

        return (string) $this->node->default->value;
    }
}

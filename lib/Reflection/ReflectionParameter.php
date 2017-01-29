<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Param;

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
    
}

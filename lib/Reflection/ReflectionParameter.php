<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Param;
use DTL\WorseReflection\Type;
use PhpParser\Node\Name;
use DTL\WorseReflection\ClassName;
use PhpParser\Node\Expr\ClassConstFetch;

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
        return Type::fromString($this->sourceContext, (string) $this->node->type);
    }
    
    public function getDefault()
    {
        if (!$this->node->default) {
            return null;
        }

        if ($this->node->default instanceof ClassConstFetch) {
            $className = $this->sourceContext->resolveClassName(ClassName::fromString((string) $this->node->default->class));
            $classReflection = $this->reflector->reflectClass($className);
            $constantReflection = $classReflection->getConstants()->get($this->node->default->name);
            return $constantReflection->getValue();
        }

        return (string) $this->node->default->value;
    }
}

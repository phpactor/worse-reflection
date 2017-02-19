<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Type;
use PhpParser\Node;
use DTL\WorseReflection\TypeResolver;

class ReflectionFrame
{
    private $frame;
    private $sourceContext;
    private $reflector;
    private $typeResolver;

    public function __construct(Reflector $reflector, SourceContext $sourceContext, Frame $frame)
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->frame = $frame;
        $this->typeResolver = new TypeResolver($reflector);
    }

    public function getType(string $name): Type
    {
        $node = $this->frame->getType($name);

        if ($node instanceof Node\Expr\MethodCall) {
            return $this->typeResolver->resolveParserNode($this, $node);
        }

        return Type::fromParserNode($this->sourceContext, $node);
    }

    public function all()
    {
        $frames = [];
        foreach ($this->frame->keys() as $name) {
            $frames[$name] = $this->getType($name);
        }

        return $frames;
    }
}

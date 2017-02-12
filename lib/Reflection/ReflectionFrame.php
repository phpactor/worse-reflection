<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Type;
use PhpParser\Node;
use DTL\WorseReflection\ChainResolver;

class ReflectionFrame
{
    private $frame;
    private $sourceContext;
    private $reflector;
    private $chainResolver;

    public function __construct(Reflector $reflector, SourceContext $sourceContext, Frame $frame)
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->frame = $frame;
        $this->chainResolver = new ChainResolver($reflector);
    }

    public function get(string $name)
    {
        $node = $this->frame->get($name);

        if ($node instanceof Node\Expr\MethodCall) {
            return $this->chainResolver->resolve($this, $node);
        }

        return Type::fromParserNode($this->sourceContext, $node);
    }

    public function all()
    {
        $frames = [];
        foreach ($this->frame->keys() as $name) {
            $frames[$name] = $this->get($name);
        }

        return $frames;
    }
}

<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Type;
use PhpParser\Node;

class ReflectionFrame
{
    private $frame;
    private $sourceContext;
    private $reflector;

    public function __construct(Reflector $reflector, SourceContext $sourceContext, Frame $frame)
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
        $this->frame = $frame;
    }

    public function get(string $name)
    {
        $node = $this->frame->get($name);

        if ($node instanceof Node\Expr\MethodCall) {
            return $this->walkChain($node);
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

    private function walkChain(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall) {
            $subjectType = $this->walkChain($node->var);
        }

        if ($node instanceof Node\Expr\Variable) {
            return $this->get($node->name);
        }

        if ($node instanceof Node\Expr\MethodCall) {
            $class = $this->reflector->reflectClass($subjectType->getClassName());
            $method = $class->getMethods()->get($node->name);

            return $method->getReturnType();
        }
    }
}

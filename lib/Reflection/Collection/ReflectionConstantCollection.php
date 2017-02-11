<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use DTL\WorseReflection\Reflection\ReflectionConstant;

class ReflectionConstantCollection extends AbstractReflectionCollection
{
    public function __construct(Reflector $reflector, SourceContext $sourceContext, ClassLike $classNode)
    {
        parent::__construct(
            'constant',
            $reflector,
            $sourceContext,
            $consts = array_reduce(array_filter($classNode->stmts, function ($stmt) {
                return $stmt instanceof ClassConst;
            }), function ($accumulator, $classConst) {
                foreach ($classConst->consts as $const) {
                    $accumulator[] = $const;
                }

                return $accumulator;
            }, [])
        );
    }

    protected function createReflectionElement(Reflector $reflector, SourceContext $sourceContext, Node $node)
    {
        return new ReflectionConstant($node->name, $node->value->value);
    }

    public function get(string $name): ReflectionConstant
    {
        return parent::get($name);
    }
}

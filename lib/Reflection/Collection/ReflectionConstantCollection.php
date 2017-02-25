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
            array_reduce(array_filter($classNode->stmts, function ($stmt) {
                return $stmt instanceof ClassConst;
            }), function ($consts, $classConst) {
                foreach ($classConst->consts as $const) {
                    $consts[$const->name] = new ReflectionConstant($const->name, $const->value->value);
                }

                return $consts;
            }, [])
        );
    }
}

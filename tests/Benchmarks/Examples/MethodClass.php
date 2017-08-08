<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks\Examples;

class MethodClass
{
    public function methodNoReturnType()
    {
    }

    public function methodWithReturnType(): MethodClass
    {
    }

    /**
     * @return MethodClass
     */
    public function methodWithDocblockReturnType()
    {
    }
}

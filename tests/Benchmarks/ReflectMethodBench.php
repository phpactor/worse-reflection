<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Tests\Benchmarks\Examples\MethodClass;
use Phpactor\WorseReflection\Reflection\ReflectionClass;

/**
 * @Iterations(10)
 * @Revs(30)
 * @OutputTimeUnit("milliseconds", precision=2)
 */
class ReflectMethodBench extends BaseBenchCase
{
    /**
     * @var ReflectionClass
     */
    private $class;

    public function before()
    {
        $this->class = $this->getReflector()->reflectClass(ClassName::fromString(MethodClass::class));
    }

    /**
     * @Subject()
     * @BeforeMethods({"before"})
     */
    public function method()
    {
        $this->class->methods()->get('methodNoReturnType');
    }

    /**
     * @Subject()
     * @BeforeMethods({"before"})
     */
    public function method_return_type()
    {
        $this->class->methods()->get('methodWithReturnType')->returnType();
    }

    /**
     * @Subject()
     * @BeforeMethods({"before"})
     */
    public function method_inferred_return_type()
    {
        $this->class->methods()->get('methodWithDocblockReturnType')->inferredReturnType();
    }
}


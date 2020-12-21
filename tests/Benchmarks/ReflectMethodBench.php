<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Tests\Benchmarks\Examples\MethodClass;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->class = $this->getReflector()->reflectClassLike(ClassName::fromString(MethodClass::class));
    }

    /**
     * @Subject()
     */
    public function method()
    {
        $this->class->methods()->get('methodNoReturnType');
    }

    /**
     * @Subject()
     */
    public function method_return_type()
    {
        $this->class->methods()->get('methodWithReturnType')->returnType();
    }

    /**
     * @Subject()
     */
    public function method_inferred_return_type()
    {
        $this->class->methods()->get('methodWithDocblockReturnType')->inferredReturnTypes()->best();
    }
}

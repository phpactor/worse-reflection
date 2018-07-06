<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod;

/**
 * @Iterations(4)
 * @Revs(1)
 * @Warmup(1)
 * @OutputTimeUnit("milliseconds", precision=2)
 */
class PhpUnitReflectClassBench extends BaseBenchCase
{
    /**
     * @Subject()
     */
    public function test_case()
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));
    }

    /**
     * @Subject()
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput", precision=2)
     */
    public function test_case_methods_and_properties()
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));

        /** @var $method ReflectionMethod */
        foreach ($class->methods() as $method) {
            foreach ($method->parameters() as $parameter) {
                $method->inferredReturnTypes();
            }
        }
    }

    /**
     * This benchmark has taken exponential amount of time (minutes), so we
     * assert that it operates in seconds rather than minutes
     * operations.
     *
     * @Subject()
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput", precision=2)
     *
     * @Assert(0.1, comparator=">", time_unit="seconds", mode="throughput", tolerance="0.5")
     */
    public function test_case_method_frames()
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));

        /** @var $method ReflectionMethod */
        foreach ($class->methods() as $method) {
            $method->frame();
        }
    }
}

<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod;

/**
 * @Iterations(4)
 * @Revs(10)
 * @Warmup(1)
 * @OutputTimeUnit("milliseconds", precision=2)
 * @Assert("variant.mode <= baseline.mode +/- 5%")
 */
class PhpUnitReflectClassBench extends BaseBenchCase
{
    /**
     * @Subject()
     * @OutputTimeUnit("microseconds", precision=2)
     */
    public function test_case(): void
    {
        $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));
    }

    /**
     * @Subject()
     * @OutputTimeUnit("seconds", precision=2)
     */
    public function test_case_methods_and_properties(): void
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));

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
     * @Revs(1)
     * @OutputTimeUnit("seconds", precision=2)
     */
    public function test_case_method_frames(): void
    {
        $class = $this->getReflector()->reflectClassLike(ClassName::fromString(TestCase::class));

        /** @var $method ReflectionMethod */
        foreach ($class->methods() as $method) {
            $method->frame();
        }
    }
}

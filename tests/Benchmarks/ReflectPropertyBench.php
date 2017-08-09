<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflector;
use Phpactor\WorseReflection\Tests\Benchmarks\Examples\PropertyClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;

/**
 * @Iterations(10)
 * @Revs(30)
 * @OutputTimeUnit("milliseconds", precision=2)
 */
class ReflectPropertyBench extends BaseBenchCase
{
    /**
     * @var ReflectionClass
     */
    private $class;

    public function before()
    {
        $this->class = $this->getReflector()->reflectClass(ClassName::fromString(PropertyClass::class));
    }

    /**
     * @Subject()
     * @BeforeMethods({"before"})
     */
    public function property()
    {
        $this->class->properties()->get('noType');
    }

    /**
     * @Subject()
     * @BeforeMethods({"before"})
     */
    public function property_return_type()
    {
        $this->class->properties()->get('withType')->type();
    }
}


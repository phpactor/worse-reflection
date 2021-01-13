<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Tests\Benchmarks\Examples\PropertyClass;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;

/**
 * @Iterations(10)
 * @Revs(30)
 * @OutputTimeUnit("milliseconds", precision=2)
 * @Assert("variant.mode <= baseline.mode +/- 10%")
 */
class ReflectPropertyBench extends BaseBenchCase
{
    /**
     * @var ReflectionClass
     */
    private $class;

    public function setUp()
    {
        parent::setUp();
        $this->class = $this->getReflector()->reflectClassLike(ClassName::fromString(PropertyClass::class));
    }

    /**
     * @Subject()
     */
    public function property()
    {
        $this->class->properties()->get('noType');
    }

    /**
     * @Subject()
     */
    public function property_return_type()
    {
        $this->class->properties()->get('withType')->inferredTypes();
    }
}

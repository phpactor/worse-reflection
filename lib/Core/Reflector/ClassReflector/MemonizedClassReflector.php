<?php

namespace Phpactor\WorseReflection\Core\Reflector\ClassReflector;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;

class MemonizedClassReflector implements ClassReflector, FunctionReflector
{
    /**
     * @var ClassReflector
     */
    private $innerReflector;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var FunctionReflector
     */
    private $functionReflector;

    public function __construct(ClassReflector $innerReflector, FunctionReflector $functionReflector)
    {
        $this->innerReflector = $innerReflector;
        $this->functionReflector = $functionReflector;
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClass($className): ReflectionClass
    {
        if ($class = $this->cachedClass($className)) {
            return $class;
        }

        return $this->putCache($className, $this->innerReflector->reflectClass($className));
    }

    /**
     * {@inheritDoc}
     */
    public function reflectInterface($className): ReflectionInterface
    {
        if ($class = $this->cachedClass($className)) {
            return $class;
        }

        return $this->putCache($className, $this->innerReflector->reflectInterface($className));
    }

    /**
     * {@inheritDoc}
     */
    public function reflectTrait($className): ReflectionTrait
    {
        if ($class = $this->cachedClass($className)) {
            return $class;
        }

        return $this->putCache($className, $this->innerReflector->reflectTrait($className));
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClassLike($className): ReflectionClassLike
    {
        if ($class = $this->cachedClass($className)) {
            return $class;
        }

        return $this->putCache($className, $this->innerReflector->reflectClassLike($className));
    }

    private function cachedClass($className)
    {
        if (isset($this->cache[(string) $className])) {
            return $this->cache[(string) $className];
        }
    }

    private function putCache($className, ReflectionClassLike $class)
    {
        $this->cache[(string) $className] = $class;

        return $class;
    }

    public function reflectFunction($name): ReflectionFunction
    {
        if ($class = $this->cachedClass($name)) {
            return $class;
        }

        return $this->putCache($name, $this->functionReflector->reflectFunction($name));
    }
}

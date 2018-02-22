<?php

namespace Phpactor\WorseReflection\Core\Reflector\ClassReflector;

use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class MemonizedClassReflector implements ClassReflector
{
    /**
     * @var ClassReflector
     */
    private $innerReflector;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(ClassReflector $innerReflector)
    {
        $this->innerReflector = $innerReflector;
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
}

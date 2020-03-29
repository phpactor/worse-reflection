<?php

namespace Phpactor\WorseReflection\Core\Reflector\ClassReflector;

use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;

class MemonizedReflector implements ClassReflector, FunctionReflector
{
    /**
     * @var ClassReflector
     */
    private $classReflector;

    /**
     * @var FunctionReflector
     */
    private $functionReflector;

    /**
     * @var ClassReflector
     */
    private $innerReflector;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(ClassReflector $innerReflector, FunctionReflector $functionReflector, Cache $cache)
    {
        $this->classReflector = $innerReflector;
        $this->functionReflector = $functionReflector;
        $this->innerReflector = $innerReflector;
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClass($className): ReflectionClass
    {
        if ($class = $this->cachedName($className)) {
            return $class;
        }

        return $this->putCache($className, $this->classReflector->reflectClass($className));
    }

    /**
     * {@inheritDoc}
     */
    public function reflectInterface($className): ReflectionInterface
    {
        if ($class = $this->cachedName($className)) {
            return $class;
        }

        return $this->putCache($className, $this->classReflector->reflectInterface($className));
    }

    /**
     * {@inheritDoc}
     */
    public function reflectTrait($className): ReflectionTrait
    {
        if ($class = $this->cachedName($className)) {
            return $class;
        }

        return $this->putCache($className, $this->classReflector->reflectTrait($className));
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClassLike($className): ReflectionClassLike
    {
        if ($class = $this->cachedName($className)) {
            return $class;
        }

        return $this->putCache($className, $this->classReflector->reflectClassLike($className));
    }

    private function cachedName($className)
    {
        if (isset($this->classCache[(string) $className])) {
            return $this->classCache[(string) $className];
        }

        return null;
    }

    private function putCache($className, $class)
    {
        $this->classCache[(string) $className] = $class;

        return $class;
    }

    public function reflectFunction($name): ReflectionFunction
    {
        if (isset($this->functionCache[(string)$name])) {
            return $this->functionCache[(string)$name];
        }

        return $this->functionCache[(string)$name] = $this->functionReflector->reflectFunction($name);
    }
}

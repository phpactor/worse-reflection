<?php

namespace Phpactor\WorseReflection\Core\Reflector\ClassReflector;

use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;
use Phpactor\WorseReflection\Core\SourceCode;

class MemonizedReflector implements ClassReflector, FunctionReflector
{
    private const FUNC_PREFIX = '__func__';
    private const CLASS_PREFIX = '__class__';
    private const INTERFACE_PREFIX = '__interface__';
    private const TRAIT_PREFIX = '__trait__';
    private const ENUM_PREFIX = '__enum__';
    private const CLASS_LIKE_PREFIX = '__class_like__';


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
        return $this->cache->getOrSet(self::CLASS_PREFIX.$className, function () use ($className) {
            return $this->classReflector->reflectClass($className);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function reflectInterface($className): ReflectionInterface
    {
        return $this->cache->getOrSet(self::INTERFACE_PREFIX.$className, function () use ($className) {
            return $this->classReflector->reflectInterface($className);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function reflectTrait($className): ReflectionTrait
    {
        return $this->cache->getOrSet(self::TRAIT_PREFIX.$className, function () use ($className) {
            return $this->classReflector->reflectTrait($className);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function reflectEnum($className): ReflectionEnum
    {
        return $this->cache->getOrSet(self::ENUM_PREFIX.$className, function () use ($className) {
            return $this->classReflector->reflectEnum($className);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClassLike($className): ReflectionClassLike
    {
        return $this->cache->getOrSet(self::CLASS_LIKE_PREFIX.(string)$className, function () use ($className) {
            return $this->classReflector->reflectClassLike($className);
        });
    }

    public function reflectFunction($name): ReflectionFunction
    {
        return $this->cache->getOrSet(self::FUNC_PREFIX.$name, function () use ($name) {
            return $this->functionReflector->reflectFunction($name);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function sourceCodeForFunction($name): SourceCode
    {
        return $this->cache->getOrSet(self::FUNC_PREFIX.'source_code'.$name, function () use ($name) {
            return $this->functionReflector->sourceCodeForFunction($name);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function sourceCodeForClassLike($name): SourceCode
    {
        return $this->cache->getOrSet(self::CLASS_LIKE_PREFIX.'source_code'.$name, function () use ($name) {
            return $this->classReflector->sourceCodeForClassLike($name);
        });
    }
}

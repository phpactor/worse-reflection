<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;

class CompositeReflector implements Reflector
{
    /**
     * @var ClassReflector
     */
    private $classReflector;

    /**
     * @var SourceCodeReflector
     */
    private $sourceCodeReflector;

    /**
     * @var FunctionReflector
     */
    private $functionReflector;

    public function __construct(
        ClassReflector $classReflector,
        SourceCodeReflector $sourceCodeReflector,
        FunctionReflector $functionReflector
    ) {
        $this->classReflector = $classReflector;
        $this->sourceCodeReflector = $sourceCodeReflector;
        $this->functionReflector = $functionReflector;
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClass($className): ReflectionClass
    {
        return $this->classReflector->reflectClass($className);
    }

    /**
     * {@inheritDoc}
     */
    public function reflectInterface($className): ReflectionInterface
    {
        return $this->classReflector->reflectInterface($className);
    }

    /**
     * {@inheritDoc}
     */
    public function reflectTrait($className): ReflectionTrait
    {
        return $this->classReflector->reflectTrait($className);
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClassLike($className): ReflectionClassLike
    {
        return $this->classReflector->reflectClassLike($className);
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClassesIn($sourceCode): ReflectionClassCollection
    {
        return $this->sourceCodeReflector->reflectClassesIn($sourceCode);
    }

    /**
     * {@inheritDoc}
     */
    public function reflectOffset($sourceCode, $offset): ReflectionOffset
    {
        return $this->sourceCodeReflector->reflectOffset($sourceCode, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function reflectMethodCall($sourceCode, $offset): ReflectionMethodCall
    {
        return $this->sourceCodeReflector->reflectMethodCall($sourceCode, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function reflectFunctionsIn($sourceCode): ReflectionFunctionCollection
    {
        return $this->sourceCodeReflector->reflectFunctionsIn($sourceCode);
    }

    public function reflectFunction($name): ReflectionFunction
    {
        return $this->functionReflector->reflectFunction($name);
    }


    /**
     * {@inheritDoc}
     */
    public function sourceCodeForClassLike($className): SourceCode
    {
        return $this->classReflector->sourceCodeForClassLike($className);
    }

    /**
     * {@inheritDoc}
     */
    public function sourceCodeForFunction($name): SourceCode
    {
        return $this->functionReflector->sourceCodeForFunction($name);
    }

    public function analyze($sourceCode): Diagnostics
    {
        return $this->sourceCodeReflector->analyze($sourceCode);
    }
}

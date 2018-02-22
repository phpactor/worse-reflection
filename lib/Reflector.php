<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\Reflector\CoreReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Logger;

interface Reflector
{
    /**
     * Reflect class.
     * 
     *         an interface or trait.
     */
    public function reflectClass($className): ReflectionClass;

    /**
     * Reflect an interface.
     * 
     * 
     *         was not a trait.
     */
    public function reflectInterface($className): ReflectionInterface;

    /**
     * Reflect a trait
     * 
     * 
     *         was not a trait.
     */
    public function reflectTrait($className): ReflectionTrait;

    /**
     * Reflect a class, trait or interface by its name.
     * 
     * If the class it not found an exception will be thrown.
     * 
     */
    public function reflectClassLike($className): ReflectionClassLike;

    /**
     * Reflect all classes (or class-likes) in the given source code.
     */
    public function reflectClassesIn($sourceCode): ReflectionClassCollection;

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     * 
     */
    public function reflectOffset($sourceCode, $offset): ReflectionOffset;

    public function reflectMethodCall($sourceCode, $offset): ReflectionMethodCall;
}

<?php

namespace Phpactor\WorseReflection\Core\Reflector;

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

interface SourceCodeReflector
{
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

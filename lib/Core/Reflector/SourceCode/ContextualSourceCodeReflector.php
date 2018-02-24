<?php

namespace Phpactor\WorseReflection\Core\Reflector\SourceCode;

use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;

class ContextualSourceCodeReflector implements SourceCodeReflector
{
    /**
     * @var SourceCodeReflector
     */
    private $innerReflector;

    /**
     * @var TemporarySourceLocator
     */
    private $locator;

    public function __construct(SourceCodeReflector $innerReflector, TemporarySourceLocator $locator)
    {
        $this->innerReflector = $innerReflector;
        $this->locator = $locator;
    }

    /**
     * {@inheritDoc}
     */
    public function reflectClassesIn($sourceCode): ReflectionClassCollection
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $this->locator->setSourceCode($sourceCode);

        $collection = $this->innerReflector->reflectClassesIn($sourceCode);

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function reflectOffset($sourceCode, $offset): ReflectionOffset
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $this->locator->setSourceCode($sourceCode);

        $offset = $this->innerReflector->reflectOffset($sourceCode, $offset);

        return $offset;
    }

    public function reflectMethodCall($sourceCode, $offset): ReflectionMethodCall
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $this->locator->setSourceCode($sourceCode);

        $offset = $this->innerReflector->reflectMethodCall($sourceCode, $offset);

        return $offset;
    }
}

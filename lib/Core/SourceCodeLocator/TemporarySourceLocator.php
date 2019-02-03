<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class TemporarySourceLocator implements SourceCodeLocator
{
    /**
     * @var SourceCode
     */
    private $source;

    /**
     * @var SourceCodeReflector
     */
    private $reflector;

    public function __construct(SourceCodeReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function pushSourceCode(SourceCode $source)
    {
        $this->source = $source;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(Name $name): SourceCode
    {
        if (null === $this->source) {
            throw new SourceNotFound('No source set on temporary locator');
        }

        $classes = $this->reflector->reflectClassesIn($this->source);

        if (false === $classes->has((string) $name)) {
            throw new SourceNotFound(sprintf(
                'Class "%s" not found',
                (string) $name
            ));
        }

        return $this->source;
    }
}

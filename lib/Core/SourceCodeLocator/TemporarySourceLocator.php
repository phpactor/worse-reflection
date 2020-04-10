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
     * @var SourceCode[]
     */
    private $sources = [];

    /**
     * @var SourceCodeReflector
     */
    private $reflector;

    public function __construct(SourceCodeReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function pushSourceCode(SourceCode $source): void
    {
        if (!$source->path()) {
            $this->sources['__source_with_no_path__'] = $source;
            return;
        }

        $this->sources[$source->path()] = $source;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(Name $name): SourceCode
    {
        foreach ($this->sources as $source) {
            $classes = $this->reflector->reflectClassesIn($source);

            if (false === $classes->has((string) $name)) {
                continue;
            }

            return $source;
        }

        throw new SourceNotFound(sprintf(
            'Class "%s" not found',
            (string) $name
        ));
    }
}

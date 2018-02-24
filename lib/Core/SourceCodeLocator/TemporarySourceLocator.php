<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

class TemporarySourceLocator implements SourceCodeLocator
{
    /**
     * @var SourceCode
     */
    private $source;

    public function setSourceCode(SourceCode $source)
    {
        $this->source = $source;
    }

    public function clear()
    {
        var_dump('Clearing');
        $this->source = null;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(ClassName $className): SourceCode
    {
        if (null === $this->source) {
            throw new SourceNotFound('No source set on temporary locator');
        }

        return $this->source;
    }
}

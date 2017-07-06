<?php

namespace Phpactor\WorseReflection\SourceCodeLocator;

use Phpactor\WorseReflection\SourceCodeLocator;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\SourceCode;

class StringSourceLocator implements SourceCodeLocator
{
    private $source;

    public function __construct(SourceCode $source)
    {
        $this->source = $source;
    }

    public function locate(ClassName $className): SourceCode
    {
        return $this->source;
    }
}

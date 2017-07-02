<?php

namespace DTL\WorseReflection\SourceCodeLocator;

use DTL\WorseReflection\SourceCodeLocator;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\SourceCode;

class StringSourceLocator implements SourceCodeLocator
{
    private $source;

    public function __construct(SourceCode $source)
    {
        $this->source = $source;
    }

    public function locate(ClassName $className): SourceCode
    {
        return $this->source;;
    }
}

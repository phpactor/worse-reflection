<?php

namespace DTL\WorseReflection\SourceLocator;

use DTL\WorseReflection\SourceLocator;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Source;

class StringSourceLocator implements SourceLocator
{
    private $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    public function locate(ClassName $className): Source
    {
        return $this->source;;
    }
}

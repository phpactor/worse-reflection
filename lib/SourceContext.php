<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\ClassName;
use PhpParser\Parser;

class SourceContext
{
    public function __construct(Source $source, Parser $parser)
    {
        foreach ($parser->parse($source->getSource()) as $statement) {
        }
    }

    public function hasClass(ClassName $className)
    {
    }
}

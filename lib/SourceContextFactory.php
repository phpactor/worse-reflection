<?php

namespace DTL\WorseReflection;

use PhpParser\Parser;

class SourceContextFactory
{
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function createFor(Source $source): SourceContext
    {
        return new SourceContext($source, $this->parser);
    }
}

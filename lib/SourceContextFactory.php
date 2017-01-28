<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\SourceContext;
use PhpParser\Parser;
use DTL\WorseReflection\Source;

class SourceContextFactory
{
    private $parser;

    public function __construct(
        Parser $parser
    )
    {
        $this->parser = $parser;
    }

    public function createFor(Source $source): SourceContext
    {
        return new SourceContext($source, $this->parser);
    }

    public function hasClass(string $classFqn): string
    {
    }
}

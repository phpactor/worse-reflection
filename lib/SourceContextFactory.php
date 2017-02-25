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

    public function createForSource(Reflector $reflector, Source $source): SourceContext
    {
        return new SourceContext($reflector, $source, $this->parser);
    }
}

<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser;

use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class TolerantDriver
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
    }

    public function sourceReflector(ServiceLocator $services): SourceCodeReflector
    {
        return new TolerantSourceCodeReflector($services);
    }
}

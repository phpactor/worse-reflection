<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflector;

use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflectorFactory;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Core\ServiceLocator;

class TolerantFactory implements SourceCodeReflectorFactory
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
    }

    public function create(ServiceLocator $serviceLocator): SourceCodeReflector
    {
        return new TolerantSourceCodeReflector($serviceLocator, $this->parser);
    }
}

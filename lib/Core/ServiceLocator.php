<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Inference\SymbolInformationResolver;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Reflector;

class ServiceLocator
{
    /**
     * @var SourceCodeLocator
     */
    private $sourceLocator;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var FrameBuilder
     */
    private $frameBuilder;

    /**
     * @var SymbolInformationResolver
     */
    private $symbolInformationResolver;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(SourceCodeLocator $sourceLocator, Logger $logger)
    {
        $this->sourceLocator = $sourceLocator;
        $this->logger = $logger;
        $this->reflector = new Reflector($this);
        $this->symbolInformationResolver = new SymbolInformationResolver($this->reflector, $this->logger);
        $this->frameBuilder = new FrameBuilder($this->symbolInformationResolver, $this->logger);
        $this->parser = new Parser();
    }

    public function reflector(): Reflector
    {
        return $this->reflector;
    }

    public function logger(): Logger
    {
        return $this->logger;
    }

    public function sourceLocator(): SourceCodeLocator
    {
        return $this->sourceLocator;
    }

    public function symbolInformationResolver(): SymbolInformationResolver
    {
        return $this->symbolInformationResolver;
    }

    public function frameBuilder(): FrameBuilder
    {
        return $this->frameBuilder;
    }

    public function parser(): Parser
    {
        return $this->parser;
    }
}

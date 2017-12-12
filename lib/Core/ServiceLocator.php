<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockFactory as DocblockFactoryBridge;
use Phpactor\WorseReflection\Core\DocblockFactory;

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
     * @var SymbolContextResolver
     */
    private $symbolContextResolver;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var DocblockFactory
     */
    private $docblockFactory;

    public function __construct(SourceCodeLocator $sourceLocator, Logger $logger)
    {
        $this->sourceLocator = $sourceLocator;
        $this->logger = $logger;
        $this->reflector = new Reflector($this);
        $this->symbolContextResolver = new SymbolContextResolver($this->reflector, $this->logger);
        $this->frameBuilder = new FrameBuilder($this->symbolContextResolver, $this->logger);
        $this->parser = new Parser();
        $this->docblockFactory = new DocblockFactoryBridge();
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

    public function symbolContextResolver(): SymbolContextResolver
    {
        return $this->symbolContextResolver;
    }

    public function frameBuilder(): FrameBuilder
    {
        return $this->frameBuilder;
    }

    public function parser(): Parser
    {
        return $this->parser;
    }

    public function docblockFactory(): DocblockFactory
    {
        return $this->docblockFactory;
    }
}

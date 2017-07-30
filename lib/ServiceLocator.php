<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Reflection\Inference\FrameBuilder;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\DocblockResolver;

class ServiceLocator
{
    /**
     * @var SouceCodeLocator
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
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function __construct(SourceCodeLocator $sourceLocator, Logger $logger)
    {
        $this->sourceLocator = $sourceLocator;
        $this->logger = $logger;
        $this->reflector = new Reflector($this);
        $this->nodeValueResolver = new NodeValueResolver($this->reflector, $this->logger);
        $this->frameBuilder = new FrameBuilder($this->nodeValueResolver);
        $this->parser = new Parser();
        $this->docblockResolver = new DocblockResolver();
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

    public function nodeValueResolver(): NodeValueResolver
    {
        return $this->nodeValueResolver;
    }

    public function frameBuilder(): FrameBuilder
    {
        return $this->frameBuilder;
    }

    public function parser(): Parser
    {
        return $this->parser;
    }

    public function docblockResolver(): DocblockResolver
    {
        return $this->docblockResolver;
    }
}

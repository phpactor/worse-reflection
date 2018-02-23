<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockFactory as DocblockFactoryBridge;
use Phpactor\WorseReflection\Core\Reflector\CoreReflector;
use Phpactor\WorseReflection\Core\Reflector\CompositeReflector;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector\MemonizedClassReflector;
use Phpactor\WorseReflection\Core\Reflector\SourceCode\ContextualSourceCodeReflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\ChainSourceLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;

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

    public function __construct(
        SourceCodeLocator $sourceLocator,
        Logger $logger,
        bool $enableCache = false,
        bool $enableContextualLocation = false
    )
    {
        $this->logger = $logger;

        $classReflector = $sourceReflector =  new CoreReflector($this);

        if ($enableCache) {
            $classReflector = new MemonizedClassReflector($classReflector);
        }

        if ($enableContextualLocation) {
            $temporarySourceLocator = new TemporarySourceLocator();
            $sourceLocator = new ChainSourceLocator([
                $sourceLocator,
                $temporarySourceLocator
            ]);
            $sourceReflector = new ContextualSourceCodeReflector($sourceReflector, $temporarySourceLocator);
        }

        $this->reflector = new CompositeReflector(
            $classReflector,
            $sourceReflector
        );

        $this->sourceLocator = $sourceLocator;
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

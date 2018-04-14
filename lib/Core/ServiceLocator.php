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
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflector\TolerantSourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflectorFactory;

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
     * @var DocBlockFactory
     */
    private $docblockFactory;

    public function __construct(
        SourceCodeLocator $sourceLocator,
        Logger $logger,
        SourceCodeReflectorFactory $reflectorFactory,
        bool $enableCache = false,
        bool $enableContextualLocation = false
    ) {
        $sourceReflector = $reflectorFactory->create($this);

        if ($enableContextualLocation) {
            $temporarySourceLocator = new TemporarySourceLocator($sourceReflector);
            $sourceLocator = new ChainSourceLocator([
                $temporarySourceLocator,
                $sourceLocator,
            ]);
            $sourceReflector = new ContextualSourceCodeReflector($sourceReflector, $temporarySourceLocator);
        }

        $classReflector = new CoreReflector($sourceReflector, $sourceLocator);

        if ($enableCache) {
            $classReflector = new MemonizedClassReflector($classReflector);
        }


        $this->reflector = new CompositeReflector(
            $classReflector,
            $sourceReflector
        );

        $this->sourceLocator = $sourceLocator;
        $this->docblockFactory = new DocblockFactoryBridge();
        $this->logger = $logger;

        $this->symbolContextResolver = new SymbolContextResolver($this->reflector, $this->logger);
        $this->frameBuilder = FrameBuilder::create($this->docblockFactory, $this->symbolContextResolver, $this->logger);
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

    public function docblockFactory(): DocBlockFactory
    {
        return $this->docblockFactory;
    }

    /**
     * TODO: This is TolerantParser specific.
     */
    public function symbolContextResolver(): SymbolContextResolver
    {
        return $this->symbolContextResolver;
    }

    /**
     * TODO: This is TolerantParser specific.
     */
    public function frameBuilder(): FrameBuilder
    {
        return $this->frameBuilder;
    }
}

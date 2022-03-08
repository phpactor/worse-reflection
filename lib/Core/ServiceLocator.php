<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Cache\NullCache;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockFactory as DocblockFactoryBridge;
use Phpactor\WorseReflection\Core\Reflector\CoreReflector;
use Phpactor\WorseReflection\Core\Reflector\CompositeReflector;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector\MemonizedReflector;
use Phpactor\WorseReflection\Core\Reflector\SourceCode\ContextualSourceCodeReflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\ChainSourceLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflectorFactory;
use Psr\Log\LoggerInterface;

class ServiceLocator
{
    /**
     * @var SourceCodeLocator
     */
    private SourceCodeLocator $sourceLocator;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Reflector
     */
    private Reflector $reflector;

    /**
     * @var FrameBuilder
     */
    private FrameBuilder $frameBuilder;

    /**
     * @var SymbolContextResolver
     */
    private SymbolContextResolver $symbolContextResolver;

    /**
     * @var DocBlockFactory
     */
    private DocBlockFactory $docblockFactory;

    /**
     * @var array<int,ReflectionMemberProvider>
     */
    private array $methodProviders;

    /**
     * @param list<FrameWalker> $frameWalkers
     * @param list<ReflectionMemberProvider> $methodProviders
     */
    public function __construct(
        SourceCodeLocator $sourceLocator,
        LoggerInterface $logger,
        SourceCodeReflectorFactory $reflectorFactory,
        array $frameWalkers,
        array $methodProviders,
        Cache $cache,
        bool $enableContextualLocation = false
    ) {
        $sourceReflector = $reflectorFactory->create($this);

        if ($enableContextualLocation) {
            $temporarySourceLocator = new TemporarySourceLocator($sourceReflector);
            $sourceLocator = new ChainSourceLocator([
                $temporarySourceLocator,
                $sourceLocator,
            ], $logger);
            $sourceReflector = new ContextualSourceCodeReflector($sourceReflector, $temporarySourceLocator);
        }

        $coreReflector = new CoreReflector($sourceReflector, $sourceLocator);

        if (!$cache instanceof NullCache) {
            $coreReflector = new MemonizedReflector($coreReflector, $coreReflector, $cache);
        }

        $this->reflector = new CompositeReflector(
            $coreReflector,
            $sourceReflector,
            $coreReflector
        );

        $this->sourceLocator = $sourceLocator;
        $this->docblockFactory = new DocblockFactoryBridge();
        $this->logger = $logger;

        $this->symbolContextResolver = new SymbolContextResolver(
            $this->reflector,
            $this->logger,
            $cache
        );
        $this->frameBuilder = FrameBuilder::create(
            $this->docblockFactory,
            $this->symbolContextResolver,
            $this->logger,
            $cache,
            $frameWalkers
        );
        $this->methodProviders = $methodProviders;
    }

    public function reflector(): Reflector
    {
        return $this->reflector;
    }

    public function logger(): LoggerInterface
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

    /**
     * @return list<ReflectionMemberProvider>
     */
    public function methodProviders(): array
    {
        return $this->methodProviders;
    }
}

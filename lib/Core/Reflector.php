<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Core\Reflection\ReflectionSourceCode;
use Phpactor\WorseReflection\Core\Reflection\AbstractReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\WorseReflection\Core\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Core\Reflection\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflector;

class Reflector
{
    /**
     * @var ServiceLocator
     */
    private $services;

    private $cache = [];

    public function __construct(ServiceLocator $services)
    {
        $this->services = $services;
    }

    public static function create(SourceCodeLocator $locator, Logger $logger = null): Reflector
    {
        $logger = $logger ?: new ArrayLogger();
        return (new ServiceLocator($locator, $logger))->reflector();
    }

    public function reflectClass(ClassName $className): AbstractReflectionClass
    {
        if (isset($this->cache[(string) $className])) {
            return $this->cache[(string) $className];
        }

        $source = $this->services->sourceLocator()->locate($className);
        $classes = $this->reflectClassesIn($source);

        if (false === $classes->has((string) $className)) {
            throw new Exception\ClassNotFound(sprintf(
                'Unable to locate class "%s"',
                $className->full()
            ));
        }

        $class = $classes->get((string) $className);
        $this->cache[(string) $className] = $class;

        return $class;
    }

    public function reflectClassesIn(SourceCode $source): ReflectionClassCollection
    {
        $node = $this->services->parser()->parseSourceFile((string) $source);

        return ReflectionClassCollection::fromSourceFileNode($this->services, $node);
    }

    public function reflectOffset(SourceCode $source, Offset $offset): ReflectionOffset
    {
        $rootNode = $this->services->parser()->parseSourceFile((string) $source);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        $resolver = $this->services->nodeValueResolver();
        $frame = $this->services->frameBuilder()->buildForNode($node);

        return ReflectionOffset::fromFrameAndValue($frame, $resolver->resolveNode($frame, $node));
    }
}

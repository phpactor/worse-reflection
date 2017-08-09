<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\AbstractReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;

class Reflector
{
    /**
     * @var ServiceLocator
     */
    private $services;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(ServiceLocator $services)
    {
        $this->services = $services;
    }

    /**
     * Create a new instance of the reflector using the given source locator
     * and optionally, a logger.
     */
    public static function create(SourceCodeLocator $locator, Logger $logger = null): Reflector
    {
        $logger = $logger ?: new ArrayLogger();
        return (new ServiceLocator($locator, $logger))->reflector();
    }

    /**
     * Reflect a class, trait or interface by its name.
     *
     * If the class it not found an exception will be thrown.
     *
     * @throws ClassNotFound
     */
    public function reflectClass(ClassName $className): AbstractReflectionClass
    {
        if (isset($this->cache[(string) $className])) {
            return $this->cache[(string) $className];
        }

        $source = $this->services->sourceLocator()->locate($className);
        $classes = $this->reflectClassesIn($source);

        if (false === $classes->has((string) $className)) {
            throw new ClassNotFound(sprintf(
                'Unable to locate class "%s"',
                $className->full()
            ));
        }

        $class = $classes->get((string) $className);
        $this->cache[(string) $className] = $class;

        return $class;
    }

    /**
     * Reflect all classes (or class-likes) in the given source code.
     */
    public function reflectClassesIn(SourceCode $source): ReflectionClassCollection
    {
        $node = $this->services->parser()->parseSourceFile((string) $source);

        return ReflectionClassCollection::fromSourceFileNode($this->services, $node);
    }

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     */
    public function reflectOffset(SourceCode $source, Offset $offset): ReflectionOffset
    {
        $rootNode = $this->services->parser()->parseSourceFile((string) $source);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        $resolver = $this->services->nodeValueResolver();
        $frame = $this->services->frameBuilder()->buildForNode($node);

        return ReflectionOffset::fromFrameAndValue($frame, $resolver->resolveNode($frame, $node));
    }
}

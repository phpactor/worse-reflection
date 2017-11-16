<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionOffset as TolerantReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\Inference\NodeReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;

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
    public static function create(SourceCodeLocator $locator = null, Logger $logger = null): Reflector
    {
        $logger = $logger ?: new ArrayLogger();
        $locator = $locator ?: new StringSourceLocator(SourceCode::fromString(''));

        return (new ServiceLocator($locator, $logger))->reflector();
    }

    /**
     * Reflect class.
     *
     * @throws ClassNotFound If the class was not found, or the class found was
     *         an interface or trait.
     */
    public function reflectClass($className): ReflectionClass
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className);

        if (false === $class instanceof ReflectionClass) {
            throw new ClassNotFound(sprintf(
                '"%s" is not a class, it is a "%s"',
                $className->full(),
                get_class($class)
            ));
        }

        return $class;
    }

    /**
     * Reflect an interface.
     *
     * @param ClassName|string $className
     *
     * @throws ClassNotFound If the class was not found, or the found class
     *         was not a trait.
     */
    public function reflectInterface($className): ReflectionInterface
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className);

        if (false === $class instanceof ReflectionInterface) {
            throw new ClassNotFound(sprintf(
                '"%s" is not an interface, it is a "%s"',
                $className->full(),
                get_class($class)
            ));
        }

        return $class;
    }

    /**
     * Reflect a trait
     *
     * @param ClassName|string $className
     *
     * @throws ClassNotFound If the class was not found, or the found class
     *         was not a trait.
     */
    public function reflectTrait($className): ReflectionTrait
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className);

        if (false === $class instanceof ReflectionTrait) {
            throw new ClassNotFound(sprintf(
                '"%s" is not a trait, it is a "%s"',
                $className->full(),
                get_class($class)
            ));
        }

        return $class;
    }

    /**
     * Reflect a class, trait or interface by its name.
     *
     * If the class it not found an exception will be thrown.
     *
     * @throws ClassNotFound
     */
    public function reflectClassLike($className): ReflectionClassLike
    {
        $className = ClassName::fromUnknown($className);

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
    public function reflectClassesIn($sourceCode): ReflectionClassCollection
    {
        return ReflectionClassCollection::fromSource($this->services, SourceCode::fromUnknown($sourceCode));
    }

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     *
     * @param SourceCode|string $sourceCode
     * @param Offset|int $offset
     */
    public function reflectOffset($sourceCode, $offset): ReflectionOffset
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $offset = Offset::fromUnknown($offset);

        $rootNode = $this->services->parser()->parseSourceFile((string) $sourceCode);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        $resolver = $this->services->symbolInformationResolver();
        $frame = $this->services->frameBuilder()->buildForNode($node);

        return TolerantReflectionOffset::fromFrameAndSymbolInformation($frame, $resolver->resolveNode($frame, $node));
    }

    public function reflectMethodCall($sourceCode, $offset): ReflectionMethodCall
    {
        $reflection = $this->reflectNode($sourceCode, $offset);

        if (false === $reflection instanceof ReflectionMethodCall) {
            throw new \RuntimeException(sprintf(
                'Expected method call, got "%s"',
                get_class($reflection)
            ));
        }

        return $reflection;
    }

    private function reflectNode($sourceCode, $offset)
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $offset = Offset::fromUnknown($offset);

        $rootNode = $this->services->parser()->parseSourceFile((string) $sourceCode);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        $frame = $this->services->frameBuilder()->buildForNode($node);
        $nodeReflector = new NodeReflector($this->services);

        return $nodeReflector->reflectNode($frame, $node);
    }
}

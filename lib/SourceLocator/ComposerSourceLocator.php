<?php

namespace DTL\WorseReflection\SourceLocator;

use DTL\WorseReflection\SourceLocator;
use DTL\WorseReflection\ClassName;
use Composer\Autoload\ClassLoader;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\Location;

class ComposerSourceLocator implements SourceLocator
{
    private $classLoader;

    public function __construct(ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(ClassName $className): Source
    {
        $path = $this->classLoader->findFile($className->getFqn());

        if (false === $path) {
            throw new Exception\SourceNotFoundException(sprintf(
                'Composer could not locate file for class "%s"',
                $className->getFqn()
            ));
        }

        return Source::fromLocation(Location::fromPath($path));
    }
}

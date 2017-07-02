<?php

namespace DTL\WorseReflection\SourceCodeLocator;

use DTL\WorseReflection\SourceCodeLocator;
use DTL\WorseReflection\ClassName;
use Composer\Autoload\ClassLoader;
use DTL\WorseReflection\SourceCode;

class ComposerSourceLocator implements SourceCodeLocator
{
    private $classLoader;

    public function __construct(ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function locate(ClassName $className): SourceCode
    {
        $path = $this->classLoader->findFile($className->getFqn());

        if (false === $path) {
            throw new \InvalidArgumentException(sprintf(
                'Composer could not locate file for class "%s"',
                $className->getFqn()
            ));
        }

        return SourceCode::fromFilepath($path);
    }
}

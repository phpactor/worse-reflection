<?php

namespace Phpactor\WorseReflection\SourceCodeLocator;

use Phpactor\WorseReflection\SourceCodeLocator;
use Phpactor\WorseReflection\ClassName;
use Composer\Autoload\ClassLoader;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\Exception\SourceNotFound;

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
        $path = $this->classLoader->findFile((string) $className);

        if (false === $path) {
            throw new SourceNotFound(sprintf(
                'Composer could not locate file for class "%s"',
                $className->getFqn()
            ));
        }

        return SourceCode::fromPath($path);
    }
}

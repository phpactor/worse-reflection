<?php

namespace Phpactor\WorseReflection\Bridge\Composer;

use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\ClassName;
use Composer\Autoload\ClassLoader;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

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
                $className->full()
            ));
        }

        return SourceCode::fromPath($path);
    }
}

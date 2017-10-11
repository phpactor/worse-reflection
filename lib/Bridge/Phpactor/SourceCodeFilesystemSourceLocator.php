<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

class SourceCodeFilesystemSourceLocator implements SourceCodeLocator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(Filesystem $filesystem, Reflector $reflector)
    {
        $this->filesystem = $filesystem;
        $this->reflector = $reflector;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(ClassName $className): SourceCode
    {
        $files = $this->filesystem->fileList()->phpFiles()->named($className->short());

        foreach ($files as $file) {
            $sourceCode = SourceCode::fromPath($file);
            $classes = $this->reflector->reflectClassesIn($sourceCode);

            if ($classes->has((string) $className)) {
                return $sourceCode;
            }
        }

        throw new SourceNotFound(sprintf('Could not locate a file for class "%s"', (string) $className));
    }
}


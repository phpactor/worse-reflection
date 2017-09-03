<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\WorseReflection\Core\ClassName as WorseClassName;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\ClassFileConverter\Domain\ClassName;

class ClassToFileSourceLocator implements SourceCodeLocator
{
    /**
     * @var ClassToFile
     */
    private $converter;

    public function __construct(ClassToFile $converter)
    {
        $this->converter = $converter;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(WorseClassName $className): SourceCode
    {
        $candidates = $this->converter->classToFileCandidates(ClassName::fromString((string) $className));

        if ($candidates->noneFound()) {
            throw new SourceNotFound(sprintf('Could not locate a candidate for "%s"', (string) $className));
        }

        foreach ($candidates as $candidate) {
            if (file_exists((string) $candidate)) {
                return SourceCode::fromPath((string) $candidate);
            }
        }

        throw new SourceNotFound($className);
    }
}

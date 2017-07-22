<?php

namespace Phpactor\WorseReflection\Tests\Integration;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\SourceCodeLocator\StringSourceLocator;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    public function createReflector(string $source)
    {
        $locator = new StringSourceLocator(SourceCode::fromString($source));
        $reflector = new Reflector($locator);

        return $reflector;
    }

    protected function workspaceDir(): string
    {
        return __DIR__ . '/../Workspace';
    }

    protected function initWorkspace()
    {
        $filesystem = new Filesystem();
        if (file_exists($this->workspaceDir())) {
            $filesystem->remove($this->workspaceDir());
        }

        $filesystem->mkdir($this->workspaceDir());
    }
}

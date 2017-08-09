<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Reflector;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

final class StubSourceLocator implements SourceCodeLocator
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var string
     */
    private $stubPath;

    public function __construct(Reflector $reflector, string $stubPath, string $cacheDir)
    {
        $this->reflector = $reflector;
        $this->stubPath = $stubPath;
        $this->cacheDir = $cacheDir;
    }

    public function locate(ClassName $className): SourceCode
    {
        $map = $this->map();

        if (isset($map[(string) $className])) {
            return SourceCode::fromPath($map[(string) $className]);
        }

        throw new SourceNotFound(sprintf(
            'Could not find source for "%s" in stub directory "%s"',
            (string) $className, $this->stubPath
        ));
    }

    private function map(): array
    {
        if (file_exists($this->serializedMapPath())) {
            return unserialize(file_get_contents($this->serializedMapPath()));
        }

        $this->buildCache();
        return $this->map();
    }

    private function buildCache()
    {
        $map = [];
        foreach($this->fileIterator() as $file) {
            if ($file->isDir()) {
                continue;
            }

            $classes = $this->reflector->reflectClassesIn(
                SourceCode::fromPath($file)
            );

            if (empty($classes)) {
                continue;
            }

            foreach ($classes as $class) {
                $map[(string) $class->name()] = (string) $file;
            }
        }

        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        file_put_contents($this->serializedMapPath(), serialize($map));
    }

    private function serializedMapPath()
    {
        return $this->cacheDir . '/stubmap.map';
    }

    private function fileIterator()
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->stubPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }
}



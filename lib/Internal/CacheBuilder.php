<?php

namespace Phpactor\WorseReflection\Internal;

class CacheBuilder
{
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
    }

    public function buildCache()
    {
        foreach($this->fileIterator as $file) {
            if ($file->isDirectory()) {
                continue;
            }
        }
    }

    private function fileIterator()
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($$this->stubPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }
}


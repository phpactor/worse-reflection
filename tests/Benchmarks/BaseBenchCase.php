<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\WorseReflection\Bridge\Composer\ComposerSourceLocator;
use Phpactor\WorseReflection\Reflector;

abstract class BaseBenchCase
{
    public function getReflector(): Reflector
    {
        $sourceLocator = new ComposerSourceLocator(include(__DIR__ . '/../../vendor/autoload.php'));
        return Reflector::create($sourceLocator);
    }
}

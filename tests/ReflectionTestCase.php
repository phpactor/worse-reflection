<?php

namespace Phpactor\WorseReflection\Tests;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\SourceCodeLocator\StringSourceLocator;

class ReflectionTestCase extends \PHPUnit_Framework_TestCase
{
    public function createReflector(string $source)
    {
        $locator = new StringSourceLocator(SourceCode::fromString($source));
        $reflector = new Reflector($locator);

        return $reflector;
    }
}

<?php

namespace DTL\WorseReflection\Tests;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceCode;
use DTL\WorseReflection\SourceCodeLocator\StringSourceLocator;

class ReflectionTestCase extends \PHPUnit_Framework_TestCase
{
    public function createReflector(string $source)
    {
        $locator = new StringSourceLocator(SourceCode::fromString($source));
        $reflector = new Reflector($locator);

        return $reflector;
    }
}

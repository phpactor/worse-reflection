<?php

namespace DTL\WorseReflection\SourceLocator;

use DTL\WorseReflection\SourceLocator;
use DTL\WorseReflection\Source;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\SourceLocator\Exception\SourceNotFoundException;

class AggregateSourceLocator implements SourceLocator
{
    private $locators;

    public function __construct(array $locators)
    {
        $this->locators = $locators;
    }

    public function locate(ClassName $className): Source
    {
        foreach ($this->locators as $locator) {
            try {
                return $locator->locate($className);
            } catch (SourceNotFoundException $e) {
            }
        }

        throw new Exception\SourceNotFoundException(sprintf(
            'Could not locate source using %d locators',
            count($this->locators)
        ), null, $e);
    }
}

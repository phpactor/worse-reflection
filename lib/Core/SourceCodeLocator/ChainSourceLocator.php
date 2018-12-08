<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

class ChainSourceLocator implements SourceCodeLocator
{
    /**
     * @var SourceCodeLocator[]
     */
    private $locators = [];

    public function __construct(array $sourceLocators)
    {
        foreach ($sourceLocators as $sourceLocator) {
            $this->add($sourceLocator);
        }
    }

    public function locate(Name $name): SourceCode
    {
        $exception = new SourceNotFound(
            'No source locators registered with chain loader '.
            '(or source locator did not throw SourceNotFound exception'
        );

        foreach ($this->locators as $locator) {
            try {
                return $locator->locate($name);
            } catch (SourceNotFound $e) {
                $exception = new SourceNotFound(sprintf(
                    'Could not find source with "%s"',
                    (string) $name
                ), null, $e);
            }
        }

        throw $exception;
    }

    private function add(SourceCodeLocator $locator)
    {
        $this->locators[] = $locator;
    }
}

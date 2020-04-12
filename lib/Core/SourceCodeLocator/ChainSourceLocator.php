<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ChainSourceLocator implements SourceCodeLocator
{
    /**
     * @var SourceCodeLocator[]
     */
    private $locators = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(array $sourceLocators, ?LoggerInterface $logger = null)
    {
        foreach ($sourceLocators as $sourceLocator) {
            $this->add($sourceLocator);
        }
        $this->logger = $logger ?: new NullLogger();
    }

    public function locate(Name $name): SourceCode
    {
        $exception = new SourceNotFound(
            'No source locators registered with chain loader '.
            '(or source locator did not throw SourceNotFound exception'
        );

        foreach ($this->locators as $locator) {
            try {
                $source = $locator->locate($name);
                $this->logger->debug(sprintf(
                    'Found source for "%s" with "%s" locator',
                    $name,
                    get_class($locator)
                ));
                return $source;
            } catch (SourceNotFound $e) {
                $this->logger->debug(sprintf(
                    'Could not find source for "%s" with "%s" locator',
                    $name,
                    get_class($locator)
                ));
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

<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Tests\Integration\Core\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;

class StubSourceLocatorTest extends IntegrationTestCase
{
    private $locator;
    private $reflector;
    private $cacheBuilder;

    public function setUp()
    {
        $this->initWorkspace();

        $locator = new StringSourceLocator(SourceCode::fromString(''));
        $reflector = Reflector::create($locator);
        $this->cacheBuilder = new StubSourceLocator(
            $reflector,
            __DIR__ . '/stubs',
            $this->workspaceDir()
        );
    }

    public function testCacheBuilder()
    {
        $code = $this->cacheBuilder->locate(ClassName::fromString('StubOne'));
        $this->assertContains('class StubOne', (string) $code);
    }
}

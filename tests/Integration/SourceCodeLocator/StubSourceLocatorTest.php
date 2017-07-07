<?php

namespace Phpactor\WorseReflection\Tests\Integration\SourceCodeLocator;

use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Stub\CacheBuilder;
use Phpactor\WorseReflection\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\ClassName;

class StubSourceLocatorTest extends IntegrationTestCase
{
    private $locator;
    private $reflector;
    private $cacheBuilder;

    public function setUp()
    {
        $this->initWorkspace();

        $locator = new StringSourceLocator(SourceCode::fromString(''));
        $reflector = new Reflector($locator);
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

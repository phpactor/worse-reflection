<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\ReflectorBuilder;

class StubSourceLocatorTest extends IntegrationTestCase
{
    private $locator;
    private $reflector;
    private $cacheBuilder;

    public function setUp()
    {
        $this->initWorkspace();

        $locator = new StringSourceLocator(SourceCode::fromString(''));
        $reflector = ReflectorBuilder::create()->addLocator($locator)->build();
        $this->cacheBuilder = new StubSourceLocator(
            $reflector,
            __DIR__ . '/stubs',
            $this->workspaceDir()
        );
    }

    public function testCanLocateClasses()
    {
        $code = $this->cacheBuilder->locate(ClassName::fromString('StubOne'));
        $this->assertContains('class StubOne', (string) $code);
    }

    public function testCanLocateFunctions()
    {
        $code = $this->cacheBuilder->locate(Name::fromString('hello_world'));
        $this->assertContains('function hello_world()', (string) $code);
    }
}

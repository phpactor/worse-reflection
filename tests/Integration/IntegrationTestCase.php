<?php

namespace Phpactor\WorseReflection\Tests\Integration;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\WorseReflection\ReflectorBuilder;

class IntegrationTestCase extends TestCase
{
    /**
     * @var ArrayLogger
     */
    private $logger;

    public function setUp()
    {
        $this->logger = new ArrayLogger();
    }

    public function createReflector(string $source): Reflector
    {
        return ReflectorBuilder::create()->addSource($source)->withLogger($this->logger())->build();
    }

    protected function workspaceDir(): string
    {
        return __DIR__ . '/../Workspace';
    }

    protected function logger(): ArrayLogger
    {
        return $this->logger;
    }

    protected function initWorkspace()
    {
        $filesystem = new Filesystem();
        if (file_exists($this->workspaceDir())) {
            $filesystem->remove($this->workspaceDir());
        }

        $filesystem->mkdir($this->workspaceDir());
    }

    protected function parseSource(string $source): SourceFileNode
    {
        $parser = new Parser();

        return $parser->parseSourceFile($source);
    }
}

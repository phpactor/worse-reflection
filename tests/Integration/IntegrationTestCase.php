<?php

namespace Phpactor\WorseReflection\Tests\Integration;

use Phpactor\TestUtils\Workspace;
use Phpactor\WorseReflection\Reflector;
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

    protected function logger(): ArrayLogger
    {
        return $this->logger;
    }

    protected function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/../Workspace');
    }

    protected function parseSource(string $source, string $uri = null): SourceFileNode
    {
        $parser = new Parser();

        return $parser->parseSourceFile($source, $uri);
    }
}

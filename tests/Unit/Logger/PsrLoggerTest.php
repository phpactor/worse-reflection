<?php

namespace Phpactor\WorseReflection\Tests\Unit\Logger;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Logger\PsrLogger;

class PsrLoggerTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    private $psr;

    /**
     * @var Logger
     */
    private $logger;

    public function setUp()
    {
        $this->psr = $this->prophesize(LoggerInterface::class);
        $this->logger = new PsrLogger($this->psr->reveal());
    }

    /**
     * @testdox It should delegate to PSR logger
     */
    public function testDelegate()
    {
        $this->psr->warning('foo')->shouldBeCalled();
        $this->logger->warning('foo');
    }
}

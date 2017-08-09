<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\PsrLog;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Bridge\PsrLog\PsrLogger;

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

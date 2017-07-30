<?php

namespace Phpactor\WorseReflection\Logger;

use Phpactor\WorseReflection\Logger;
use Psr\Log\LoggerInterface;

final class PsrLogger implements Logger
{ 
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function warning(string $message)
    {
        $this->logger->warning($message);
    }

    public function debug(string $message)
    {
        $this->logger->debug($message);
    }
}

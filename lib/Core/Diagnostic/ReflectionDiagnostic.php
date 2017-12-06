<?php

namespace Phpactor\WorseReflection\Core\Diagnostic;

use Phpactor\WorseReflection\Core\Diagnostic;

class ReflectionDiagnostic implements Diagnostic
{
    /**
     * @var string
     */
    private $message;

    private function __construct(string $message, $reflection)
    {
        $this->message = $message;
        $this->reflection = $reflection;
    }

    public function fromMessageAndReflection(string $message, $reflection)
    {
        return new self($message, $reflection);
    }
}

<?php

namespace Phpactor\WorseReflection\Core\Logger;

use Phpactor\WorseReflection\Core\Logger;

class ArrayLogger implements Logger
{
    private $messages = [];

    public function warning(string $message)
    {
        $this->messages[] = $message;
    }

    public function debug(string $message)
    {
        $this->messages[] = $message;
    }

    public function messages(): array
    {
        return $this->messages;
    }
}


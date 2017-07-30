<?php

namespace Phpactor\WorseReflection\Logger;

use Phpactor\WorseReflection\Logger;

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


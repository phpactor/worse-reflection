<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

class ArrayLogger implements Logger
{
    private $messages = [];

    public function warning(string $message)
    {
        $this->messages[] = $message;
    }

    public function messages(): array
    {
        return $this->messages;
    }
}


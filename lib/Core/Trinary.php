<?php

namespace Phpactor\WorseReflection\Core;

class Trinary
{
    private ?bool $true;

    private function __construct(?bool $true)
    {
        $this->true = $true;
    }

    public function TRUE(): self
    {
        return new self(true);
    }

    public function FALSE(): self
    {
        return new self(false);
    }

    public function MAYBE(): self
    {
        return new self(null);
    }
}

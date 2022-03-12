<?php

namespace Phpactor\WorseReflection\Core;

class Trinary
{
    private ?bool $true;

    private function __construct(?bool $true)
    {
        $this->true = $true;
    }

    public static function true(): self
    {
        return new self(true);
    }

    public static function false(): self
    {
        return new self(false);
    }

    public static function maybe(): self
    {
        return new self(null);
    }
}

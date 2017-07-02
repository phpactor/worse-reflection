<?php

namespace DTL\WorseReflection;

class SourceCode
{
    private $source;

    private function __construct(string $source)
    {
        $this->source = $source;
    }

    public static function fromString($string)
    {
        return new self($string);
    }

    public function __toString()
    {
        return $this->source;
    }
}

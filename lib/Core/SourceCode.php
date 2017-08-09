<?php

namespace Phpactor\WorseReflection\Core;

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

    public static function fromPath(string $filePath)
    {
        if (!file_Exists($filePath)) {
            throw new \InvalidArgumentException(sprintf(
                'File "%s" does not exist',
                $filePath
            ));
        }

        return new self(file_get_contents($filePath));
    }

    public function __toString()
    {
        return $this->source;
    }
}

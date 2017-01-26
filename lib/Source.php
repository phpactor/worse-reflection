<?php

namespace DTL\WorseReflection;

class Source
{
    private $filepath;
    private $source;

    public static function fromFilepath($filepath)
    {
        if (!file_exists($filepath)) {
            throw new \InvalidArgumentException(sprintf(
                'File "%s" does not exist',
                $filepath
            ));
        }

        $instance = new self();
        $instance->filepath = $filepath;
        $instance->source = file_get_contents($filepath);

        return $instance;
    }
}

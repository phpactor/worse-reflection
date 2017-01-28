<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

class Source
{
    private $location;
    private $source;

    public static function fromString($string)
    {
        $instance = new self();
        $instance->source = $string;

        return $instance;
    }

    public static function fromFilepath($location)
    {
        if (!file_exists($location)) {
            throw new \InvalidArgumentException(sprintf(
                'File "%s" does not exist',
                $location
            ));
        }

        $instance = new self();
        $instance->location = $location;
        $instance->source = file_get_contents(realpath($location));

        return $instance;
    }

    public function getLocation(): string
    {
        return $this->source;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}

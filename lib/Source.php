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
        $instance->location = Location::fromNothing();

        return $instance;
    }

    public static function fromLocation(Location $location)
    {
        if (false === $location->exists()) {
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

    public function hasLocation(): bool
    {
        return null !== $this->location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}

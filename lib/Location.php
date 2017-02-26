<?php

namespace DTL\WorseReflection;

final class Location
{
    private $path;

    private function __construct()
    {
    }

    public static function fromPath(string $path)
    {
        $instance = new self();
        $instance->path = $path;

        return $instance;
    }

    public function getPath() 
    {
        return $this->path;
    }

    public function isAbsolute()
    {
        return substr($this->path, 0, 1) === '/';
    }

    public static function fromNothing()
    {
        $instance = new self();

        return $instance;
    }

    public function exists()
    {
        return file_exists($this->path);
    }

    public function __toString()
    {
        if ($this->path) {
            return $this->path;
        }

        return '<in memory>';
    }

}

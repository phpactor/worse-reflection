<?php

namespace DTL\WorseReflection;

class Namespace_
{
    private $namespace;

    private function __construct()
    {
    }

    public static function fromString(string $namespace)
    {
        $instance = new self();
        $instance->namespace = $namespace;

        return $instance;
    }

    public function getFqn()
    {
        return $this->namespace;
    }
}


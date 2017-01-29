<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\ClassName;

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

    public static function fromParts(array $parts)
    {
        $instance = new self();
        $instance->namespace = implode('\\', $parts);

        return $instance;
    }

    public function spawnClassName($shortName)
    {
        return ClassName::fromNamespaceAndShortName($this, $shortName);
    }

    public function getFqn()
    {
        return $this->namespace;
    }

    public function isRoot()
    {
        return empty($this->namespace);
    }
}


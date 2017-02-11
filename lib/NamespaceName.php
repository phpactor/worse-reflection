<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\ClassName;

class NamespaceName implements NameLike
{
    private $name;

    private function __construct()
    {
    }

    public static function fromParts(array $parts): NamespaceName
    {
        return self::fromName(Name::fromParts($parts));
    }

    public static function fromString(string $string): NamespaceName
    {
        return self::fromName(Name::fromString($string));
    }

    public static function fromName(Name $name)
    {
        $instance = new self();
        $instance->name = $name; 

        return $instance;
    }

    public function spawnClassName($shortName): ClassName
    {
        return ClassName::fromNamespaceAndShortName($this, $shortName);
    }

    public function getFqn(): string
    {
        return $this->name->getFqn();
    }

    public function getParts(): array
    {
        return $this->name->getParts();
    }

    public function isRoot()
    {
        return empty($this->name->getParts());
    }
}


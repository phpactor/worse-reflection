<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

class Name implements NameLike
{
    private $parts;

    private function __construct()
    {
    }

    public static function fromParts(array $parts): Name
    {
        $instance = new self();
        $instance->parts = $parts;

        return $instance;
    }

    public static function fromString(string $fqn): Name
    {
        return self::fromParts(array_filter(explode('\\', $fqn), function ($part) {
            return '' !== $part;
        }));
    }

    public static function fromNamespaceAndShortName(NamespaceName $namespace, string $shortName)
    {
        $fqn = $namespace->isRoot() ? $shortName : $namespace->getFqn() . '\\' . $shortName;

        return self::fromString($fqn);
    }

    public function getFqn(): string
    {
        return implode('\\', $this->parts);
    }

    public function getShortName(): string
    {
        return end($this->parts);
    }

    public function getParts(): array
    {
        return $this->parts;
    }
}

<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

class ClassName
{
    private $parts;

    private function __construct()
    {
    }

    public static function fromParts(array $parts): ClassName
    {
        if ([] === $parts) {
            throw new \RuntimeException(
                'Class name must have at least one part'
            );
        }

        $instance = new self();
        $instance->parts = $parts;

        return $instance;
    }

    public static function fromString(string $fqn): ClassName
    {
        return self::fromParts(explode('\\', $fqn));
    }

    public static function fromNamespaceAndShortName(Namespace_ $namespace, string $shortName)
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
}

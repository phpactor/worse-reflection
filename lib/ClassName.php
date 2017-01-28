<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

class ClassName
{
    private $classFqn;

    private function __construct()
    {
    }

    public static function fromFqn(string $classFqn)
    {
        $instance = new self();
        $instance->classFqn = $classFqn;

        return $instance;
    }

    public static function fromNamespaceAndShortName(Namespace_ $namespace, string $shortName)
    {
        $instance = new self();
        $instance->classFqn = $namespace->getFqn() . '\\' . $shortName;

        return $instance;
    }

    public static function fromFqnParts(array $parts)
    {
        $instance = new self();
        $instance->classFqn = implode('\\', $parts);

        return $instance;
    }

    public function getFqn()
    {
        return $this->classFqn;
    }
}

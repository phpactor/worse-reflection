<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use DTL\WorseReflection\Name;

class ClassName implements NameLike
{
    /**
     * @var Name
     */
    private $name;

    public static function fromName(Name $name)
    {
        $instance = new self();
        $instance->name = $name; 

        return $instance;
    }

    public static function fromParts(array $parts): ClassName
    {
        if ([] === $parts) {
            throw new \RuntimeException(
                'Class name must have at least one part'
            );
        }

        return self::fromName(Name::fromParts($parts));
    }

    public static function fromString(string $fqn): ClassName
    {
        return self::fromName(Name::fromString($fqn));
    }

    public static function fromNamespaceAndShortName(Namespace_ $namespace, string $shortName)
    {
        $fqn = $namespace->isRoot() ? $shortName : $namespace->getFqn() . '\\' . $shortName;

        return self::fromString($fqn);
    }

    public function getFqn(): string
    {
        return $this->name->getFqn();
    }

    public function getShortName(): string
    {
        return $this->name->getShortName();
    }

    public function getParts(): array
    {
        return $this->name->getParts();
    }
}

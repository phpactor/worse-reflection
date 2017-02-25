<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use PhpParser\Node;


class ClassName implements NameLike
{
    /**
     * @var Name
     */
    private $name;

    /**
     * @var bool
     */
    private $isFullyQualified = false;

    public static function fromParserName(Node\Name $name)
    {
        $instance = self::fromParts($name->parts);
        if ($name instanceof Node\Name\FullyQualified) {
            $instance->isFullyQualified = true;
        }

        return $instance;
    }

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

    public static function fromNamespaceAndShortName(NamespaceName $namespace, string $shortName)
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

    public function isFullyQualified(): bool
    {
        return $this->isFullyQualified;
    }
}

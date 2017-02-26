<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use PhpParser\Node;
use DTL\WorseReflection\NamespaceName;


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
        $instance = self::fromName(Name::fromString($fqn));
        if (substr($fqn, 0, 1) === '\\') {
            $instance->isFullyQualified = true;
        }

        return $instance;
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

    public function getNamespaceName(): NamespaceName
    {
        $parts = $this->name->getParts();
        array_pop($parts);
        return NamespaceName::fromParts($parts);
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

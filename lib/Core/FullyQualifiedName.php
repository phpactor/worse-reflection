<?php

namespace Phpactor\WorseReflection\Core;

class FullyQualifiedName
{
    private $parts;

    public function __construct(array $parts)
    {
        $this->parts = $parts;
    }

    public static function fromString(string $string)
    {
        if (empty($string)) {
            return new static([]);
        }

        $parts = explode('\\', trim($string, '\\'));

        return new static($parts);
    }

    public static function root(): FullyQualifiedName
    {
        return new static([]);
    }

    public function isRoot()
    {
        return empty($this->parts);
    }

    public static function fromUnknown($value)
    {
        if ($value instanceof ClassName) {
            return $value;
        }

        if (is_string($value)) {
            return self::fromString($value);
        }

        throw new \InvalidArgumentException(sprintf(
            'Do not know how to create class from type "%s"',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    public function __toString()
    {
        return implode('\\', $this->parts);
    }

    public function full(): string
    {
        return $this->__toString();
    }

    public function short(): string
    {
        return end($this->parts);
    }
}

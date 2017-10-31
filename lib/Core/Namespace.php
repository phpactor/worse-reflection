<?php

namespace Phpactor\WorseReflection\Core;

class ClassName
{
    private $parts;

    public function __construct(array $parts)
    {
        $this->parts = $parts;
    }

    public static function fromString(string $string)
    {
        $parts = explode('\\', trim($string, '\\'));

        return new self($parts);
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

    public function namespace(): string
    {
        if (count($this->parts) === 1) {
            return '';
        }

        return implode('\\', array_slice($this->parts, 0, count($this->parts) - 1));
    }
}

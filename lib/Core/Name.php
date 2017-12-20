<?php

namespace Phpactor\WorseReflection\Core;

class Name
{
    protected $parts;

    public function __construct(array $parts)
    {
        $this->parts = $parts;
    }

    public static function fromParts(array $parts)
    {
        return new static($parts);
    }

    public static function fromString(string $string)
    {
        $parts = explode('\\', trim($string, '\\'));

        return new static($parts);
    }

    public static function fromUnknown($value)
    {
        if ($value instanceof Name) {
            return $value;
        }

        if (is_string($value)) {
            return static::fromString($value);
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

    /**
     * @return static
     */
    public function head()
    {
        return new self([ reset($this->parts) ]);
    }

    /**
     * @return static
     */
    public function tail()
    {
        $parts = $this->parts;
        array_shift($parts);
        return new self($parts);
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

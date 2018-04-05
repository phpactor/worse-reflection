<?php

namespace Phpactor\WorseReflection\Core;

use RuntimeException;
use InvalidArgumentException;

class Name
{
    // see: http://php.net/manual/en/language.oop5.basic.php
    const NAME_REGEX = '{^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-]+$}';

    protected $parts;
    private $wasFullyQualified;

    final public function __construct(array $parts, bool $wasFullyQualified)
    {
        static $count;
        echo $count++ . PHP_EOL;
        $this->validateParts($parts);
        $this->parts = $parts;
        $this->wasFullyQualified = $wasFullyQualified;
    }

    public static function fromParts(array $parts)
    {
        return new static($parts, false);
    }

    public static function fromString(string $string)
    {
        $fullyQualified = 0 === strpos($string, '\\');
        $parts = explode('\\', trim($string, '\\'));

        return new static($parts, $fullyQualified);
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
        return new self([ reset($this->parts) ], false);
    }

    /**
     * @return static
     */
    public function tail()
    {
        $parts = $this->parts;
        array_shift($parts);
        return new self($parts, $this->wasFullyQualified);
    }

    public function full(): string
    {
        return $this->__toString();
    }

    public function short(): string
    {
        return end($this->parts);
    }

    public function wasFullyQualified(): bool
    {
        return $this->wasFullyQualified;
    }

    /**
     * @return static
     */
    public function prepend($name)
    {
        $name = Name::fromUnknown($name);
        return self::fromString(join('\\', [(string) $name, $this->__toString()]));
    }

    private function validateParts(array $parts)
    {
        array_map(function ($part) use ($parts) {
            if (!preg_match(self::NAME_REGEX, $part)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid class name "%s"',
                    implode('\\', $parts)
                ));
            }
        }, $parts);
    }
}

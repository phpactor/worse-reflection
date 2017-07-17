<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\ClassName;

class Type
{
    const TYPE_ARRAY = 'array';
    const TYPE_BOOL = 'bool';
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_CLASS = 'object';

    private $type;
    private $className;

    public static function fromString(string $type): Type
    {
        if ('' === $type) {
            return self::unknown();
        }

        if ($type === 'string') {
            return self::string();
        }

        if ($type === 'int') {
            return self::int();
        }

        if ($type === 'float') {
            return self::float();
        }

        if ($type === 'array') {
            return self::array();
        }

        if ($type === 'bool') {
            return self::bool();
        }

        if ($type === 'mixed') {
            return self::mixed();
        }


        return self::class(ClassName::fromString($type));
    }

    public static function unknown()
    {
        return new self();
    }

    public static function mixed()
    {
        return new self();
    }

    public static function array()
    {
        return self::create(self::TYPE_ARRAY);
    }

    public static function bool()
    {
        return self::create(self::TYPE_BOOL);
    }

    public static function string()
    {
        return self::create(self::TYPE_STRING);
    }

    public static function int()
    {
        return self::create(self::TYPE_INT);
    }

    public static function float()
    {
        return self::create(self::TYPE_FLOAT);
    }

    public static function class(ClassName $className)
    {
        $instance = new self();
        $instance->type = self::TYPE_CLASS;
        $instance->className = $className;

        return $instance;
    }

    public static function none()
    {
        return new self();
    }

    public function isNone()
    {
        return null === $this->type;
    }

    public function __toString()
    {
        return $this->className ? (string) $this->className : $this->type ?: '<unknown>';
    }

    /**
     * Return the short name of the type, whether it be a scalar or class name.
     */
    public function short(): string
    {
        if ($this->isPrimitive()) {
            return $this->type;
        }

        return $this->className->short();
    }

    public function isPrimitive(): bool
    {
        return $this->className === null;
    }

    public function primitive(): string
    {
        return $this->type;
    }

    public function className()
    {
        return $this->className;
    }

    private static function create($type)
    {
        $instance = new self();
        $instance->type = $type;

        return $instance;
    }
}

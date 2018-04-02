<?php

namespace Phpactor\WorseReflection\Core;

class Type
{
    const TYPE_ARRAY = 'array';
    const TYPE_BOOL = 'bool';
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_CLASS = 'object';
    const TYPE_NULL = 'null';
    const TYPE_VOID = 'void';

    private $type;
    private $className;
    private $arrayType;

    public static function fromArray(array $parts): Type
    {
        return self::fromString(implode('\\', $parts));
    }

    public static function fromValue($value): Type
    {
        if (is_int($value)) {
            return self::int();
        }

        if (is_string($value)) {
            return self::string();
        }

        if (is_float($value)) {
            return self::float();
        }

        if (is_array($value)) {
            return self::array();
        }

        if (is_bool($value)) {
            return self::bool();
        }

        if (null === $value) {
            return self::null();
        }

        if (is_object($value)) {
            return self::class(ClassName::fromString(get_class($value)));
        }

        return self::unknown();
    }

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

        if ($type === 'null') {
            return self::null();
        }

        if ($type === 'void') {
            return self::void();
        }

        return self::class(ClassName::fromString($type));
    }

    public static function unknown()
    {
        return new self();
    }

    /**
     * TODO: Support "pseudo" types
     */
    public static function mixed()
    {
        return new self();
    }

    public static function void()
    {
        return self::create(self::TYPE_VOID);
    }

    public static function array(string $type = null)
    {
        $instance = self::create(self::TYPE_ARRAY);

        if ($type) {
            $instance->arrayType = Type::fromString($type);
            return $instance;
        }

        $instance->arrayType = self::unknown();

        return $instance;
    }

    public static function collection(string $type, string $iterableType)
    {
        $instance = self::create($type);
        $instance->className = ClassName::fromString($type);
        $instance->arrayType = self::fromString($iterableType);

        return $instance;
    }

    public static function null()
    {
        return self::create(self::TYPE_NULL);
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

    public static function undefined()
    {
        return new self();
    }

    public function isDefined()
    {
        return null !== $this->type;
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
            return (string) $this->type;
        }

        return (string) $this->className->short();
    }

    public function isPrimitive(): bool
    {
        return $this->className === null;
    }

    public function isClass(): bool
    {
        return $this->className !== null;
    }

    public function primitive(): string
    {
        return $this->type;
    }

    /**
     * @return ClassName
     */
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

    public function prependNamespace(Name $namespace)
    {
        return self::class(ClassName::fromString((string) $namespace . '\\' . (string) $this));
    }

    public function arrayType(): Type
    {
        if ($this->arrayType) {
            return $this->arrayType;
        }

        return self::unknown();
    }
}

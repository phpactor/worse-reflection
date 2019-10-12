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

    /**
     * @var string
     */
    private $phpType;

    /**
     * @var ClassName
     */
    private $className;

    /**
     * @var Type|null
     */
    private $arrayType;

    public function __construct(string $phpType = null)
    {
        $this->phpType = $phpType;
    }

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

        if ($type === 'object') {
            return self::object();
        }

        if ($type === 'null') {
            return self::null();
        }

        if ($type === 'void') {
            return self::void();
        }

        return self::class(ClassName::fromString($type));
    }

    public static function unknown(): Type
    {
        return new self(null);
    }

    /**
     * TODO: Support "pseudo" types
     */
    public static function mixed(): Type
    {
        return new self(null);
    }

    public static function void(): Type
    {
        return self::create(self::TYPE_VOID);
    }

    public static function array(string $type = null): Type
    {
        $instance = self::create(self::TYPE_ARRAY);

        if ($type) {
            $instance->arrayType = Type::fromString($type);
            return $instance;
        }

        $instance->arrayType = self::unknown();

        return $instance;
    }

    public static function collection(string $type, string $iterableType): Type
    {
        $instance = self::class(ClassName::fromString($type));
        $instance->arrayType = self::fromString($iterableType);

        return $instance;
    }

    public static function null(): Type
    {
        return self::create(self::TYPE_NULL);
    }

    public static function bool(): Type
    {
        return self::create(self::TYPE_BOOL);
    }

    public static function string(): Type
    {
        return self::create(self::TYPE_STRING);
    }

    public static function int(): Type
    {
        return self::create(self::TYPE_INT);
    }

    public static function float(): Type
    {
        return self::create(self::TYPE_FLOAT);
    }

    private static function object()
    {
        return self::create(self::TYPE_CLASS);
    }

    public static function class($className): Type
    {
        $className = ClassName::fromUnknown($className);
        $instance = new self($className->full());
        $instance->phpType = self::TYPE_CLASS;
        $instance->className = $className;

        return $instance;
    }

    public static function undefined(): Type
    {
        return new self(null);
    }

    public function isDefined(): bool
    {
        return null !== $this->phpType;
    }

    public function __toString()
    {
        $className = $this->className ? (string) $this->className : $this->phpType ?: '<unknown>';

        if (null === $this->arrayType) {
            return $className;
        }

        if ($this->isClass()) {
            return $className . '<' . $this->arrayType->__toString() . '>';
        }

        if ($this->arrayType->isDefined()) {
            return (string) $this->arrayType . '[]';
        }

        return $className;
    }

    /**
     * Return the short name of the type, whether it be a scalar or class name.
     */
    public function short(): string
    {
        if ($this->isPrimitive()) {
            return (string) $this->phpType;
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
        return $this->phpType;
    }

    /**
     * @return ClassName|null
     */
    public function className()
    {
        return $this->className;
    }

    private static function create($type): Type
    {
        return new self($type);
    }

    public function arrayType(): Type
    {
        if ($this->arrayType) {
            return $this->arrayType;
        }

        return self::unknown();
    }

    public function withArrayType(Type $arrayType): Type
    {
        $clone = clone $this;
        $clone->arrayType = $arrayType;
        return $clone;
    }

    public function withClassName(string $className): Type
    {
        $clone = clone $this;
        $clone->className = ClassName::fromUnknown($className);

        return $clone;
    }

    public function __clone()
    {
        if ($this->className) {
            $this->className = clone $this->className;
        }

        if ($this->arrayType) {
            $this->arrayType = clone $this->arrayType;
        }
    }
}

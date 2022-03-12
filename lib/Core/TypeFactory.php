<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\IterableType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\ResourceType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Type\VoidType;

class TypeFactory
{
    public static function fromString(string $type): Type
    {
        if ('?' === substr($type, 0, 1)) {
            return self::nullable(self::typeFromString(substr($type, 1)));
        }

        return self::typeFromString($type);
    }

    private static function typeFromString(string $type): Type
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

        if ($type === 'object') {
            return self::object();
        }

        if ($type === 'void') {
            return self::void();
        }

        if ($type === 'callable') {
            return self::callable();
        }

        if ($type === 'resource') {
            return self::resource();
        }

        if ($type === 'iterable') {
            return self::iterable();
        }

        if ($type === 'self') {
            return new SelfType();
        }

        if ($type === 'static') {
            return new StaticType();
        }

        if ($type === '$this') {
            return new StaticType();
        }

        return self::class(ClassName::fromString($type));
    }

    public static function union(Type ...$types): UnionType
    {
        return new UnionType(...$types);
    }

    public static function null(): NullType
    {
        return new NullType();
    }

    public static function unknown(): MissingType
    {
        return new MissingType();
    }

    public static function string(): StringType
    {
        return new StringType(null);
    }

    public static function int(): IntType
    {
        return new IntType();
    }

    public static function float(): FloatType
    {
        return new FloatType();
    }

    public static function array(): IterableType
    {
        return new IterableType(new MissingType(), new MissingType());
    }

    public static function mixed(): MixedType
    {
        return new MixedType();
    }

    public static function bool(): BooleanType
    {
        return new BooleanType();
    }

    public static function object(): ClassType
    {
        return new ClassType(null);
    }

    public static function void(): VoidType
    {
        return new VoidType();
    }

    public static function resource(): ResourceType
    {
        return new ResourceType();
    }

    public static function iterable(): IterableType
    {
        return new IterableType(new MissingType(), new MissingType());
    }

    /**
     * @param string|ClassName $className
     */
    public static function class($className): ClassType
    {
        return new ClassType(ClassName::fromUnknown($className));
    }

    public static function undefined(): MissingType
    {
        return new MissingType();
    }

    public static function callable(): CallableType
    {
        return new CallableType([], new MissingType());
    }

    public static function nullable(Type $type): NullableType
    {
        return new NullableType($type);
    }
}

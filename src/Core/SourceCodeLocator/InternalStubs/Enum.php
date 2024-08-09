<?php

class UnitEnumCase
{
    public string $name;
}

class BackedEnumCase extends UnitEnumCase
{
    public int|string $value;
}

interface UnitEnum
{
    /**
     * @return UnitEnumCase[]
     */
    public static function cases(): array;
}

interface BackedEnum extends UnitEnum
{
    /**
     * @return BackedEnumCase[]
     */
    public static function cases(): array;

    /**
     * @param int|string $value
     */
    public static function from($value): static;

    /**
     * @param int|string $value
     */
    public static function tryFrom($value): ?static;
}

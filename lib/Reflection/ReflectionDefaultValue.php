<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Reflection\ReflectionDefaultValue;

final class ReflectionDefaultValue
{
    private $value;
    private $none = false;

    private function __construct($value = null)
    {
        $this->value = $value;
    }

    public static function fromValue($value): ReflectionDefaultValue
    {
        return new self($value);
    }

    public static function none(): ReflectionDefaultValue
    {
        $new = new self();
        $new->none = true;

        return $new;
    }

    public function isNone(): bool
    {
        return $this->none;
    }

    public function value()
    {
        return $this->value;
    }
}

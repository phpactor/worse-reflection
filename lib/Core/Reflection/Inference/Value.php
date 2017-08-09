<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Inference\Value;

final class Value
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var Type
     */
    private $type;

    private function __construct(Type $type, $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public static function fromTypeAndValue(Type $type, $value): Value
    {
         return new self($type, $value);
    }

    public static function fromType(Type $type)
    {
        return new self($type);
    }

    public static function none()
    {
        return new self(Type::undefined());
    }

    public function withValue($value)
    {
        return self::fromTypeAndValue($this->type, $value);
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function value()
    {
        return $this->value;
    }
}


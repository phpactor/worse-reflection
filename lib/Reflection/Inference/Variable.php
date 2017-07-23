<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\Offset;

final class Variable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Offset
     */
    private $offset;

    /**
     * @var mixed
     */
    private $value;

    private function __construct(string $name, Offset $offset, Value $value)
    {
        $this->name = $name;
        $this->offset = $offset;
        $this->value = $value;
    }

    public static function fromOffsetNameTypeAndValue(int $offset, string $name, string $type, $value): Variable
    {
         return new self($name, Offset::fromInt($offset), Value::fromTypeAndValue(Type::fromString($type), $value));
    }

    public static function fromOffsetNameAndType(int $offset, string $name, string $type): Variable
    {
         return new self($name, Offset::fromInt($offset), Value::fromType(Type::fromString($type)));
    }

    public function __toString()
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): Value
    {
        return $this->value;
    }
}
